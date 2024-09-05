<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Sandbox;
use App\Services\GitRepositoryService;

class ResetSandboxReleaseBranchAction extends AbstractAction
{
    protected const ACTION_NAME = 'reset-sandbox-release-branch';

    public function execute(Sandbox $sandbox): void
    {
        $branchName = $sandbox->release->release_branch_name;

        $gitRepoService = app(GitRepositoryService::class);

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
