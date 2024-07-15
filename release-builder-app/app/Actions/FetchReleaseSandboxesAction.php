<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Release;
use App\Services\GitRepositoryService;

class FetchReleaseSandboxesAction extends AbstractAction
{
    protected const ACTION_NAME = 'fetch-repositories';

    public function execute(Release $release): void
    {
        $gitRepoService = app(GitRepositoryService::class);

        foreach ($release->sandboxes as $sandbox) {
            $sandboxRepo = $gitRepoService->getServiceRepository($sandbox);

            $start = microtime(true);

            try {
                $sandboxRepo->fetch();

                $this->log('Success. Time: ' . microtime(true) - $start, $sandboxRepo->getPath());
            } catch (\Exception $e) {
                $this->log('Error: ' . $e->getMessage(), $sandboxRepo->getPath());
            }
        }
    }
}
