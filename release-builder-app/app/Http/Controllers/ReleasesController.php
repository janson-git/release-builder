<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewReleaseRequest;
use App\Lib\Git\GitException;
use App\Lib\Git\GitRepository;
use App\Models\Release;
use App\Models\Sandbox;
use App\Models\Service;
use App\Services\GitRepositoryService;
use App\Services\SandboxRepositoryService;
use Illuminate\Log\Logger;

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

    public function mergeBranches(int $id)
    {
        $release = Release::find($id);

        $branches = $release->branches;
        // always get latest master, main branches
        array_unshift($branches, 'master', 'main');

        $gitRepoService = app(GitRepositoryService::class);

        $actionLog = [];

        // TODO: LOG all operations and show errors
        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->fetch();
            $sandboxRepo->fullReset();
            $sandboxRepo->checkout($release->release_branch_name);
            $this->_mergeBranches($sandboxRepo, $branches, $actionLog);
        }

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Merge branches results",
            'release' => $release,
            'action' => 'Merge Branches',
            'actionLog' => $actionLog,
        ]);
    }

    private function _mergeBranches(GitRepository $repo, array $branches, array &$log = [], int $loop = 1): array
    {
        $unmerged    = [];
        $results     = [];
        $mergedCount = 0;

        foreach ($branches as $branch) {
            try {
                $result = $repo->mergeRemoteIfHas($branch);
                if ($result !== false) {
                    $results[$branch] = $result;
                    $mergedCount++;
                } else {
                    $results[$branch] = 'Doesn\'t exist in ' . $repo->getPath();
                }
            } catch (GitException $e) {
                $exceptionOutput = $e->getOutput();
                $results[$branch] = 'Error: ' . $e->getMessage() . ". Trace: \n" . implode("\n", $exceptionOutput);
                $repo->fullReset();
                $unmerged[] = $branch;
            }
        }

        $log[] = [
            'command' => 'merge branches',
            'options' => $branches,
            'repoPath' => $repo->getPath(),
            'results' => $results,
        ];

        // private const MERGE_RETRIES_LIMIT = 5;
        if ($mergedCount && count($unmerged) > 0 && $loop < 5) {
            $this->_mergeBranches($repo, $unmerged, $log, ++$loop);
        }

        return $log;
    }
}
