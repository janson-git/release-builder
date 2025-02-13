<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Sandbox;
use App\Services\GitRepositoryService;

class PushSandboxReleaseBranchAction extends AbstractAction
{
    protected const ACTION_NAME = 'push-sandbox-release-branch';

    public function execute(Sandbox $sandbox): void
    {
        $gitRepoService = app(GitRepositoryService::class);

        $releaseBranch = $sandbox->release->release_branch_name;

        $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

        $sandboxRepo->fetch();
        $sandboxRepo->checkout($releaseBranch);
        $sandboxRepo->push([$releaseBranch]);

        $this->log($sandboxRepo->getLastOutput(), $sandboxRepo->getPath());
    }
}
