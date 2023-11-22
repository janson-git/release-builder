<?php

namespace Commands\Command\Pack;

use Commands\Command\CommandProto;
use Commands\CommandConfig;

class RemoveCheckpoint extends CommandProto
{
    public function run()
    {
        $branchName    = $this->context->getCheckpoint()->getName();
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->checkoutToOriginMainBranch();
            try {
                $repo->removeBranch($branchName);
                $this->runtime[$repo->getPath()] = 'success';
            } catch (\Exception $e) {
                $this->runtime[$repo->getPath()] = $e->getMessage();
            }
        }
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_DELETE;
    }
    
    public function getHumanName()
    {
        return __('remove_build');
    }
    
    public function isConfirmRequired(): bool
    {
        return true;
    }

    public function isDanger(): bool
    {
        return true;
    }
}
