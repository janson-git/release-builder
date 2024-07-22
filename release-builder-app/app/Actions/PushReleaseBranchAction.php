<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Release;
use App\Services\GitRepositoryService;

class PushReleaseBranchAction extends AbstractAction
{
    protected const ACTION_NAME = 'push-release-branch';

    public function execute(Release $release): void
    {
        $gitRepoService = app(GitRepositoryService::class);

        $releaseBranch = $release->release_branch_name;

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->fetch();
            $sandboxRepo->checkout($releaseBranch);
            $sandboxRepo->push([$releaseBranch]);

            $this->log($sandboxRepo->getLastOutput(), $sandboxRepo->getPath());
        }
    }
}
