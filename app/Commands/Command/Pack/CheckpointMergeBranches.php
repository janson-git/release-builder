<?php

namespace Commands\Command\Pack;

use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Git\GitRepository;

class CheckpointMergeBranches extends CommandProto
{
    private const MERGE_RETRIES_LIMIT = 5;

    public function run()
    {
        $pack           = $this->context->getPack();
        $checkpointName = $this->context->getCheckpoint()->getName();
        $branches       = $pack->getBranches();

        // always get latest master, main branches
        array_unshift($branches, 'master', 'main');

        foreach ($pack->getRepos() as $id => $repo) {
            $repo->fetch();
            $repo->fullReset();
            $repo->checkout($checkpointName);
            $this->_mergeBranches($repo, $branches);
        }

        return $this->runtime;
    }
    
    private function _mergeBranches(GitRepository $repo, array $branches, int $loop = 1): void
    {
        $unmerged    = [];
        $results     = [];
        $mergedCount = 0;
        
        foreach ($branches as $branch) {
            try {
                $result = $repo->mergeRemoteIfHas($branch);
                if ($result !== false) {
                    $results[$branch] = $result;
                    $mergedCount++;
                }
            } catch (\Exception $e) {
                $results[$branch] = 'Error: ' . $e->getMessage();
                $this->runtime->exception($e);
                $repo->fullReset();
                $unmerged[] = $branch;
            }
        }

        $this->runtime->log($results, $repo->getPath());
        
        if ($mergedCount && $loop < self::MERGE_RETRIES_LIMIT) {
            $this->_mergeBranches($repo, $unmerged, ++$loop);
        }
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_MERGE_BRANCHES;
    }
    
    public function getHumanName()
    {
        return __('merge_branches');
    }
    
    public function isPrimary(): bool
    {
        return true;
    }
}
