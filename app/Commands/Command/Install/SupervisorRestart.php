<?php

namespace Commands\Command\Install;

use Commands\Command\CommandProto;
use Commands\CommandConfig;

class SupervisorRestart extends CommandProto
{
    public function run()
    {
        $res = $this->getSlot()->stdExec('sudo service supervisor restart', __METHOD__);
        $this->runtime->log($res, 'supervisor restart'); 
    }
    
    public function getId()
    {
        return CommandConfig::SUPERVISOR_RESTART;
    }
    
    public function getHumanName()
    {
        return 'Перезапустить супервизор';
    }
}
