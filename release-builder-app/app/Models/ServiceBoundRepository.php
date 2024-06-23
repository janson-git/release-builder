<?php

declare(strict_types=1);

namespace App\Models;

use App\Lib\Git\GitRepository;
use App\Services\GitRepositoryService;

/**
 * Wrapper based on GitRepositoryService.
 * Just to handle all requests to GIT repository that related to Service, but
 * we don't want hold these methods in Service model directly
 */
class ServiceBoundRepository
{
    private GitRepository $gitRepository;
    private GitRepositoryService $gitRepositoryService;

    public function __construct(
        private Service $service
    ) {
        $this->gitRepositoryService = app(GitRepositoryService::class);
        $this->gitRepository = $this->gitRepositoryService->getServiceRepository($this->service);
    }

    public function getAllBranches(): array
    {
        return $this->gitRepository->getAllBranches();
    }

    public function getRemotes()
    {
        return $this->gitRepository->getRemotes();
    }

    public function getFilesUpdateTime(): array
    {
        return $this->gitRepositoryService->getFilesUpdateTime($this->service);
    }
}
