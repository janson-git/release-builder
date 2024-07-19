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

    private const MERGE_RETRIES_LIMIT = 5;

    public function execute(Release $release): void
    {
        $branches = $release->branches;
        // always get latest master, main branches
        array_unshift($branches, 'master', 'main');

        $gitRepoService = app(GitRepositoryService::class);
        $releaseBranchName = $release->release_branch_name;

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->fetch();
            $sandboxRepo->fullReset();

            if (!$sandboxRepo->isBranchExists($releaseBranchName)) {
                $sandboxRepo->checkoutToNewBranchFromOriginMain($releaseBranchName);
            } else {
                $sandboxRepo->checkout($releaseBranchName);
            }
            $this->_mergeBranches($sandboxRepo, $branches);
        }
    }

    private function _mergeBranches(GitRepository $repo, array $branches, int $loop = 1): void
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
                }
                // omit for not exists branches
            } catch (GitException $e) {
                $exceptionOutput = $e->getOutput();
                $results[$branch] = 'Error: ' . $e->getMessage() . ". Trace: \n" . implode("\n", $exceptionOutput);
                $repo->fullReset();
                $unmerged[] = $branch;
            }
        }

        $this->log($results, $repo->getPath());

        // private const MERGE_RETRIES_LIMIT = 5;
        if ($mergedCount && count($unmerged) > 0 && $loop < self::MERGE_RETRIES_LIMIT) {
            $this->_mergeBranches($repo, $unmerged, ++$loop);
        }
    }
}
