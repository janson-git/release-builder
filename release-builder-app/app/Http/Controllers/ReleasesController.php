<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewReleaseRequest;
use App\Http\Requests\UpdateReleaseRequest;
use App\Lib\Git\GitRepository;
use App\Models\Release;
use App\Models\Service;
use App\Services\GitRepositoryService;

class ReleasesController extends Controller
{
    public function index()
    {
        $releases = Release::orderBy('id', 'desc')->get();

        return response()->view('releases.index', [
            'header' => 'Releases',
            'subheader' => "{$releases->count()} releases on this page",
            'releaseList' => $releases,
        ]);
    }

    public function show(int $id)
    {
        $release = Release::findOrFail($id);

        $gitRepoService = app(GitRepositoryService::class);

//        $branches = $gitRepoService->getBranchesWithServices($allServices);
        $branchesDiffs = $gitRepoService->getToMasterStatus($release->branches, $release->services);

        return response()->view('releases.show', [
            'header' => $release->name,
            'release' => $release,
            'branchesDiffs' => $branchesDiffs,
        ]);
    }

    public function create()
    {
        $gitRepoService = app(GitRepositoryService::class);

        $allServices = Service::all();
        foreach ($allServices as $service) {
            $serviceRepo = $gitRepoService->getServiceRepository($service);
            $serviceRepo->fetch();
        }

        $branches = $gitRepoService->getBranchesWithServices($allServices);

        return response()->view('releases.create', [
            'header' => 'Releases',
            'servicesList' => $allServices,
            'branches' => $branches,
            'selected' => [],
        ]);
    }

    public function store(NewReleaseRequest $request)
    {
        $release = new Release();
        $release->name = $request->getReleaseName();
        $release->branches = $request->getBranches();
        $release->save();

        $release->services()->sync($request->getServiceIds(), false);

        // create release branch in related sandboxes
        $gitRepoService = app(GitRepositoryService::class);

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->fetch();
            $sandboxRepo->fullReset();
            $sandboxRepo->checkoutToNewBranchFromOriginMain($release->release_branch_name);
        }

        return redirect()->route('releases');
    }

    public function edit(int $id)
    {
        $release = Release::findOrFail($id);

        $gitRepoService = app(GitRepositoryService::class);

        $allServices = Service::all();
        $branches = $gitRepoService->getBranchesWithServices($allServices);
        $branchesDiffs = $gitRepoService->getToMasterStatus($release->branches, $release->services);

        return response()->view('releases.edit', [
            'header' => 'Releases',
            'servicesList' => $allServices,
            'branches' => $branches,
            'selected' => [],
            'release' => $release,
            'branchesDiffs' => $branchesDiffs,
        ]);
    }

    public function update(int $id, UpdateReleaseRequest $request)
    {
        $release = Release::findOrFail($id);

        $release->name = $request->getReleaseName();
        $release->branches = $request->getBranches();
        $release->save();

        $release->services()->sync($request->getServiceIds());

        // create release branch in related sandboxes
        $gitRepoService = app(GitRepositoryService::class);

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            // if it is added service and repo, need create release branch
            if (!$sandboxRepo->isBranchExists($release->release_branch_name)) {
                $sandboxRepo->fetch();
                $sandboxRepo->fullReset();
                $sandboxRepo->checkoutToNewBranchFromOriginMain($release->release_branch_name);
            }
        }

        return redirect("/releases/{$release->id}");
    }

    public function destroy(int $id)
    {
        /** @var Release $release */
        $release = Release::findOrFail($id);

        $releaseBranch = $release->release_branch_name;

        $services = Service::all();
        $sandboxesStorage = \Storage::disk('sandboxes');

        $user = auth()->user();
        //  1. remove release branches in ALL sandbox repositories!
        foreach ($services as $service) {
            if ($service->directory !== 'ssh/docker-dev-template') {
                continue;
            }
            if ($sandboxesStorage->has($service->directory)) {

                $repository = app(GitRepository::class, [
                    'repository' => $sandboxesStorage->path($service->directory),
                    'user' => $user,
                ]);

                $repository->fetch();
                $repository->checkoutToMainBranch();

                $branchExists = $repository->isBranchExists($releaseBranch);
                $remoteBranchExists = $repository->isRemoteBranchExists($releaseBranch);

                // ! DANGER ZONE ! Deletion from remote repository!
                if ($branchExists) {
                    $repository->deleteBranch($releaseBranch);
                }
                if ($remoteBranchExists) {
                    $repository->deleteRemoteBranch($releaseBranch);
                }
            }
        }

        // 2. remove related sandboxes records from DB
        $release->services()->sync([]);
        // 3. remove release
        $release->delete();

        return redirect()->route('releases');
    }
}
