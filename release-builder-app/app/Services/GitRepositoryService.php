<?php

declare(strict_types=1);

namespace App\Services;

use App\Lib\Git\GitRepository;
use App\Lib\Git\Utils\StringHelper;
use App\Models\Service;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GitRepositoryService
{
    private array $initializedRepos = [];

    public function cloneRepository(Service $service): void
    {
        if ($this->hasClonedRepository($service)) {
            throw new \Exception("Directory '{$service->directory}' already exists and not empty");
        }

        $this->storage()->createDirectory($service->directory);

        $gitRepository = $this->getServiceRepository($service);

        $gitRepository->cloneRemoteRepository(
                $service->repository_url,
                $this->storage()->path($service->directory)
            );

        $output = $gitRepository->getLastOutput();

        app(Logger::class)->debug($output);
    }

    private function hasClonedRepository(Service $service): bool
    {
        $storage = $this->storage();
        return ($storage->directoryExists($service->directory)
            && count($storage->listContents($service->directory)->toArray()) > 0);
    }

    public function getRemoteBranches(Service $service): array
    {
        if (!$this->hasClonedRepository($service)) {
            return [];
        }

        $gitRepository = $this->getServiceRepository($service);
        return $gitRepository->getRemoteBranches() ?? [];
    }

    /**
     * @param Collection|Service[] $serviceList
     * @return array
     */
    public function scanBranches(Collection $serviceList)
    {
        $branchesByServices = [];

        foreach ($serviceList as $service) {
            $branches = $this->getRemoteBranches($service);

            foreach ($branches as $branchOrderId => &$branch) {
                if (str_starts_with($branch, 'HEAD')) {
                    unset($branches[$branchOrderId]);
                }
            }

            $branchesByServices[$service->name] = $branches;
        }

        return $branchesByServices;
    }

    /**
     * @param Collection|Service[] $serviceList
     * @return array
     * [
     *   'branch-a' => ['service_1' => 'service_1'],
     *   'branch-b' => ['service_1' => 'service_1'],
     *   'branch-c' => ['service_1' => 'service_1', 'service_2' => 'service_2'],
     * ]
     */
    public function getBranchesWithServices(Collection $serviceList): array
    {
        $serviceList = $serviceList->keyBy('name');

        $scannedBranches = $this->scanBranches($serviceList);
        $commonBranches = [];
        foreach ($scannedBranches as $serviceName => $dirBranches) {
            foreach ($dirBranches as $branch) {
                $commonBranches[$branch][$serviceName] = $serviceName;
            }
        }
        $branches = array_keys($commonBranches);
        array_multisort($branches, SORT_NATURAL, $commonBranches);
        return array_combine($branches, $commonBranches);
    }

    // REFACTOR Node method to V3

    /**
     * @param array $branches
     * @param Collection|Service[] $services
     * @return mixed
     * @throws \App\Lib\Git\GitException
     */
    public function getToMasterStatus(array $branches, Collection $services)
    {
        // FIXME: STILL NEED FIX. But getBehindStatus() by default works with
        // FIXME:   local branches diff. It fails if local repo has not branch
        // FIXME:   copies locally
        foreach ($services as $service) {
            $repo = $this->getServiceRepository($service);
            foreach ($branches as $branch => $value) {
                $diff = $repo->getBehindStatus($branch);
            }
        }
//        $branchIdx = array_flip($branches);
//        $repoIdToDirs = array_flip($this->dirs);
//
//        $scannedBranches;
//        foreach ($this->branchesByRepoDirs as $repoDir => $dirBranches) {
//            foreach ($dirBranches as $branch) {
//                if (!isset($branchIdx[$branch])) {
//                    continue;
//                }
//
//                $this->branchesToMasterStatus[$branch][$repoDir] = $this->repos[$repoIdToDirs[$repoDir]]->getBehindStatus('origin/'.$branch);
//            }
//        }
//
//        $branchesResult = array_intersect_key($this->branchesToMasterStatus, $branchIdx);
//
//        $branches = array_keys($branchesResult);
//        array_multisort($branches, SORT_NATURAL, $branchesResult);
//
//        return array_combine($branches, $branchesResult);
    }

    public function getFilesUpdateTime(Service $service): array
    {
        $path = $this->storage()->path($service->directory);
        $time = filemtime($path);

        return [
            'back'      => StringHelper::howMuchAgo($time),
            'date'      => date('d-m h:i', $time),
            'timestamp' => $time,
        ];
    }

    private function storage(): FilesystemAdapter
    {
        return Storage::disk('repositories');
    }

    public function getRepositoryDirNameFromUrl(string $repositoryPath): string
    {
        $cleanedRepoPath = preg_replace('#[^a-zA-Z0-9:@./\-]#', '', $repositoryPath);

        $repoNameFull = mb_substr($cleanedRepoPath, strrpos($cleanedRepoPath, '/') + 1);
        return str_replace('.git', '', $repoNameFull);
    }

    public function getServiceRepository(Service $service): GitRepository
    {
        if (!$this->hasClonedRepository($service)) {
            throw new \Exception('Service repository still has not cloned');
        }

        if (array_key_exists($service->repository_url, $this->initializedRepos)) {
            return $this->initializedRepos[$service->repository_url];
        }

        $repository = app(GitRepository::class, [
            'repository' => $this->storage()->path($service->directory)
        ]);

        $this->initializedRepos[$service->repository_url] = $repository;

        return $repository;
    }
}
