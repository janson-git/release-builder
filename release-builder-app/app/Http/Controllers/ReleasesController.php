<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Service;
use App\Services\GitRepositoryService;

class ReleasesController extends Controller
{
    public function index()
    {
        return response()->view('releases.index', [
            'header' => 'Releases',
            'releaseList' => Release::all(),
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
            'header' => 'Services',
            'servicesList' => $allServices,
            'branches' => $branches,
            'selected' => [],
        ]);
    }

    public function show(int $id)
    {
        $release = Release::find($id);

        return response()->view('releases.show', [
            'header' => 'Releases',
            'release' => $release,
        ]);
    }
}
