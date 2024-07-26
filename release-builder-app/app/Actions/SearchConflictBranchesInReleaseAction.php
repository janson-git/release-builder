<?php

declare(strict_types=1);

namespace App\Actions;

use App\Lib\Git\GitException;
use App\Lib\Git\GitRepository;
use App\Models\Release;
use App\Services\GitRepositoryService;

class SearchConflictBranchesInReleaseAction extends AbstractAction
{
    protected const ACTION_NAME = 'search-conflict-branches-in-release';

    private array $knownPairs = [];
    private int $troubles = 0;

    public function execute(Release $release): void
    {
        $branches = $release->branches;

        $gitRepoService = app(GitRepositoryService::class);

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $conflict = $this->_findConflictBranches($sandboxRepo, $branches);

            if ($conflict) {
                $sandbox->markAsConflicted();
                $this->log($conflict, 'CONFLICTED');

                $testBranches = $branches;
                array_unshift($testBranches, 'master', 'main');

                foreach ($conflict as $conflictBranch) {
                    $this->_findConflictPairs($sandboxRepo, $conflictBranch, $testBranches);
                }
            } else {
                $sandbox->markAsGood();
            }
        }
    }


    /**
     * @param GitRepository $repo
     * @param     $branches
     * @param int $loop
     *
     * @return array|mixed
     */
    private function _findConflictBranches(GitRepository $repo, array $branches) {
        $mergeTestBranch = 'merge-test-'.date('Y.m.d\a\tH-i-s');
        $repoPath = $repo->getPath();

        $repo->fullReset();
        $repo->checkoutToMainBranch();
        $repo->checkoutToNewBranchFromOriginMain($mergeTestBranch);

        $conflict = [];
        $results = [];

        foreach ($branches as $branch) {
            try {
                $result = $repo->mergeRemoteIfHas($branch);
                if ($result !== false) {
                    $results[$branch] = ['ok'];
                } else {
                    $results[$branch] = ['doesn\'t exists'];
                }
            } catch (GitException $e) {
                $repo->fullReset();
                $conflict[] = $branch;
//                $this->results[$repo->getPath()][1][$branch] = 'Conflicted: '.implode("\n", $e->getOutput());
            }
        }

        $this->log($results, $repo->getPath());

        $repo->checkoutToMainBranch();
        $repo->removeBranch($mergeTestBranch);

        return $conflict;
    }

    /**
     * @param $repo GitRepository
     * @param $conflictBranch
     * @param $testBranches
     */
    private function _findConflictPairs(GitRepository $repo, $conflictBranch, $testBranches)
    {
        $repo->fullReset();
        $troubles = [];

        foreach ($testBranches as $testBranch) {
            if ($testBranch === $conflictBranch) {
                continue;
            }
            $repo->checkoutToMainBranch();

            $mergeTestBranch = 'merge-test-find-'.$conflictBranch.microtime(true);
            $repo->checkoutToNewBranch('origin/'.$conflictBranch, $mergeTestBranch);

            try {
                $repo->mergeRemoteIfHas($testBranch);
            } catch (GitException $e) {
                $this->knownPairs[$testBranch][$conflictBranch] = 1;
                $this->knownPairs[$conflictBranch][$testBranch] = 1;
                $this->troubles++;

                $troubles['#'.$this->troubles] = [
                    'REPO' => $repo->getPath(),
                    'TROUBLE' => $conflictBranch.' TO '.$testBranch,
                    'MERGE_BRANCH' => 'merge-'.date('md').'-'.$conflictBranch.'-to-'.$testBranch,
                    'DESC' => $e->getOutput(),
                    'DIFF' => $repo->diff() . "\n",
                ];
                $repo->fullReset();
            }

            $repo->checkoutToMainBranch();
            $repo->removeBranch($mergeTestBranch);
        }

        $this->log($troubles, 'TROUBLES');
    }

}
