<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Release;
use App\Services\GitRepositoryService;

class GitCreateTagAction extends AbstractAction
{
    protected const ACTION_NAME = 'git-create-tag';

    public function __construct(protected string $tag)
    {

    }

    public function execute(Release $release): void
    {
        $gitRepoService = app(GitRepositoryService::class);

        // TODO: ssh key or token to enable push repos
        $sshPrivateKey = null;
        $releaseBranch = $release->release_branch_name;

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->setSshKeyPath($sshPrivateKey);

            $sandboxRepo->fetch();
            $sandboxRepo->checkout($releaseBranch);
            $sandboxRepo->createTag($this->tag);
            $sandboxRepo->pushTags();

            $sandboxRepo->setSshKeyPath(null);

            $this->log($sandboxRepo->getLastOutput(), $sandboxRepo->getPath());
        }
    }
}
