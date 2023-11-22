<?php

namespace Commands\Command\Pack;

use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Service\Util\Fs;

class RemovePackWithData extends CommandProto
{
    public function run()
    {
        $pack = $this->context->getPack();
        $path = $pack->getPath();
        
        $log = Fs::i()->stdExec('rm -rf ' . $path, __METHOD__);
        $this->runtime->log($log, 'Sandbox remove');

        $pack->delete();
        
        $this->runtime->log("Pack {$pack->getId()} removed.", 'Pack remove');
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::PACK_CLEAR_DATA;
    }
    
    public function getHumanName()
    {
        return __('delete_pack');
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