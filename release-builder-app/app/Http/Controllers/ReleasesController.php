<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewReleaseRequest;
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

        return response()->view('releases.show', [
            'header' => $release->name,
            'release' => $release,
        ]);
    }

    public function create()
    {
        $gitRepoService = app(GitRepositoryService::class);

        $allServices = Service::all();

        $branches = $gitRepoService->getBranchesWithServices($allServices);

        // TODO: move/refactor Node->getToMasterStatus() from old version
        // TODO: we need enrich branches with difference from 'master'
//        $packReposByBranches = $this->node->getToMasterStatus($this->packBranches);

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
}
