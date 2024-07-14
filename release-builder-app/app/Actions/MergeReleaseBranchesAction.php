<?php

declare(strict_types=1);

namespace App\Actions;

use App\Lib\Git\GitException;
use App\Lib\Git\GitRepository;
use App\Models\Release;
use App\Services\GitRepositoryService;

class MergeReleaseBranchesAction extends AbstractAction
{
    protected const ACTION_NAME = 'merge-release-branches';

    public function execute(Release $release): void
    {
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

        $this->log($results, $repo->getPath());

        // private const MERGE_RETRIES_LIMIT = 5;
        if ($mergedCount && count($unmerged) > 0 && $loop < 5) {
            $this->_mergeBranches($repo, $unmerged, $log, ++$loop);
        }

        return $log;
    }
}
