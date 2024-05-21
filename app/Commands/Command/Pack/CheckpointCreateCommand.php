<?php

namespace Commands\Command\Pack;

use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Commands\PackOwnerAuthorityTrait;

class CheckpointCreateCommand extends CommandProto
{
    use PackOwnerAuthorityTrait;
    
    private string $checkpointName = '';

    public function prepare()
    {
        $sandbox = $this->context->getPack();
        $this->checkpointName = $sandbox->getName() . '-' . date('Ymd-His');
        $sandbox->cloneMissedRepos();
    }
    
    public function run()
    {
        $sandbox = $this->context->getPack();
        foreach ($sandbox->getRepos() as $id => $repo) {
            $repo->fetch();
            $repo->fullReset();
            $repo->checkoutToNewBranchFromOriginMain($this->checkpointName);
            $this->runtime[$repo->getPath()] = ['ok '.$this->checkpointName];
        }
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_CREATE;
    }
    
    public function getHumanName()
    {
        return __('create_new_build');
    }
}
