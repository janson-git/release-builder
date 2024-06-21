<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Service;
use App\Services\GitRepositoriesService;

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
        $gitRepoService = app(GitRepositoriesService::class);

        $allServices = Service::all();


        //--- TODO: SHOULD BE A FUNCTION
        $scannedBranches = $gitRepoService->scanBranches($allServices);
        $commonBranches = [];
        foreach ($scannedBranches as $repoDir => $dirBranches) {
            foreach ($dirBranches as $branch) {
                $commonBranches[$branch][$repoDir] = $repoDir;
            }
        }
        $branches = array_keys($commonBranches);
        array_multisort($branches, SORT_NATURAL, $commonBranches);
        $branches = array_combine($branches, $commonBranches);
        //---

        // TODO: move/refactor Node->getToMasterStatus() from old version
        // TODO: we need enrich branches with difference from 'master'

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
