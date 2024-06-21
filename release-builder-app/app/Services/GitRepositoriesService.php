<?php

declare(strict_types=1);

namespace App\Services;

use App\Lib\Git\GitRepository;
use App\Models\Service;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GitRepositoriesService
{
    public function cloneRepository(string $repositoryPath)
    {
        $dirName = $this->getRepositoryDirName($repositoryPath);

        if ($this->hasClonedRepository($repositoryPath)) {
            throw new \Exception("Directory '{$dirName}' already exists and not empty");
        }

        $this->storage()->createDirectory($dirName);

        $gitService = app(GitRepository::class, [
            'repository' => $this->storage()->path($dirName)
        ]);

        $gitService->cloneRemoteRepository(
                $repositoryPath,
                $this->storage()->path($dirName)
            );

        $output = $gitService->getLastOutput();

        app(Logger::class)->debug($output);
    }

    public function hasClonedRepository(string $repositoryPath): bool
    {
        $dirName = $this->getRepositoryDirName($repositoryPath);

        $storage = $this->storage();
        return ($storage->directoryExists($dirName)
            && count($storage->listContents($dirName)->toArray()) > 0);
    }

    public function getServiceLocalBranches(Service $service): array
    {
        if (!$this->hasClonedRepository($service->repository_url)) {
            return [];
        }

        $dirName = $this->getRepositoryDirName($service->repository_url);

        $gitService = app(GitRepository::class, [
            'repository' => $this->storage()->path($dirName)
        ]);

        return $gitService->getBranches() ?? [];
    }

    public function getServiceRemoteBranches(Service $service): array
    {
        if (!$this->hasClonedRepository($service->repository_url)) {
            return [];
        }

        $dirName = $this->getRepositoryDirName($service->repository_url);

        $gitService = app(GitRepository::class, [
            'repository' => $this->storage()->path($dirName)
        ]);

        return $gitService->getRemoteBranches() ?? [];
    }

    /**
     * @param Collection|Service[] $serviceList
     * @return array
     */
    public function scanBranches(Collection $serviceList)
    {
        $branchesByServices = [];

        foreach ($serviceList as $service) {
            $branches = $this->getServiceRemoteBranches($service);

            foreach ($branches as $branchOrderId => &$branch) {
                if (str_starts_with($branch, 'HEAD')) {
                    unset($branches[$branchOrderId]);
                }
            }

            $branchesByServices[$service->repository_url] = $branches;
        }

        return $branchesByServices;
    }

    private function storage(): FilesystemAdapter
    {
        return Storage::disk('repositories');
    }

    private function getRepositoryDirName(string $repositoryPath): string
    {
        $cleanedRepoPath = preg_replace('#[^a-zA-Z0-9:@./\-]#', '', $repositoryPath);

        $repoNameFull = mb_substr($cleanedRepoPath, strrpos($cleanedRepoPath, '/') + 1);
        return str_replace('.git', '', $repoNameFull);
    }
}
