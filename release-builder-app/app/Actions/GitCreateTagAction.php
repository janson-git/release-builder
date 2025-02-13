<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Release;
use App\Services\GitRepositoryService;

class GitCreateTagAction extends AbstractAction
{
    protected const ACTION_NAME = 'git-create-tag';

    public function __construct(
        protected string $tag
    ){
    }

    public function execute(Release $release): void
    {
        $gitRepoService = app(GitRepositoryService::class);

        $releaseBranch = $release->release_branch_name;

        // TODO: перед созданием тега нужно проверить, что во всех
        // TODO:  репозиториях это возможно. Что тег уникальный для всех.
        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $sandboxRepo->fetch();
            $sandboxRepo->checkout($releaseBranch);
            $sandboxRepo->createTag($this->tag);
            $sandboxRepo->pushTags();

            $this->log($sandboxRepo->getLastOutput(), $sandboxRepo->getPath());
        }
    }
}
