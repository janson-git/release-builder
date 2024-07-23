<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Release;
use App\Services\GitRepositoryLinkable;
use App\Services\GitRepositoryService;

class FetchReleaseRepositoriesAction extends AbstractAction
{
    protected const ACTION_NAME = 'fetch-release-repositories';

    private ?GitRepositoryService $gitRepositoryService;

    public function execute(Release $release): void
    {
        $this->gitRepositoryService = new GitRepositoryService();

        foreach ($release->sandboxes as $sandbox) {
            $this->fetchRepository($sandbox);
        }
        foreach ($release->services as $service) {
            $this->fetchRepository($service);
        }
    }

    private function fetchRepository(GitRepositoryLinkable $repo): void
    {
        $chargedRepo = $this->gitRepositoryService->getServiceRepository($repo);

        $start = microtime(true);

        try {
            $chargedRepo->fetch();

            $this->log('Success. Time: ' . microtime(true) - $start, $chargedRepo->getPath());
        } catch (\Exception $e) {
            $this->log('Error: ' . $e->getMessage(), $chargedRepo->getPath());
        }
    }
}
