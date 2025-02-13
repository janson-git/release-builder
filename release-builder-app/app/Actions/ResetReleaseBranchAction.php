<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Release;
use App\Services\GitRepositoryService;

class ResetReleaseBranchAction extends AbstractAction
{
    protected const ACTION_NAME = 'reset-release-branch';

    public function execute(Release $release): void
    {
        $branchName = $release->release_branch_name;

        $gitRepoService = app(GitRepositoryService::class);

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->checkoutToOriginMainBranch();
            try {
                $sandboxRepo->removeBranch($branchName);
                $sandboxRepo->checkoutToNewBranchFromOriginMain($branchName);

                $this->log('success', $sandboxRepo->getPath());
            } catch (\Exception $e) {
                $this->log('Error: ' . $e->getMessage(), $sandboxRepo->getPath());
            }
        }
    }
}
