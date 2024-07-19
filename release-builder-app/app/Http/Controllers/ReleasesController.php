<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewReleaseRequest;
use App\Http\Requests\UpdateReleaseRequest;
use App\Models\Release;
use App\Models\Service;
use App\Services\GitRepositoryService;

class ReleasesController extends Controller
{
    public function index()
    {
        $releases = Release::all();
        return response()->view('releases.index', [
            'header' => 'Releases',
            'subheader' => "{$releases->count()} releases on this page",
            'releaseList' => $releases,
        ]);
    }

    public function show(int $id)
    {
        $release = Release::find($id);

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
        $release = Release::find($id);

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
        $release = Release::find($id);

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
}
