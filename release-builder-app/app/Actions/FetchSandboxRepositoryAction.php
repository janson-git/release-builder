<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Sandbox;
use App\Services\GitRepositoryLinkable;
use App\Services\GitRepositoryService;

class FetchSandboxRepositoryAction extends AbstractAction
{
    protected const ACTION_NAME = 'fetch-sandbox-repository';

    private ?GitRepositoryService $gitRepositoryService;

    public function execute(Sandbox $sandbox): void
    {
        $this->gitRepositoryService = new GitRepositoryService();

        $this->fetchRepository($sandbox);
        $this->fetchRepository($sandbox->service);
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
