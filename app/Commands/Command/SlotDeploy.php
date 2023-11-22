<?php

namespace Commands\Command;

use Commands\CommandConfig;
use Commands\CommandFlow;
use Service\Event\EventConfig;

class SlotDeploy extends DeployCommandProto
{
    /**
     * @var CommandProto[]
     */
    protected $commands = [];

    public function run()
    {
        if (!$this->context->getSlot()) {
            $this->runtime->log('Слот не назначен');
            return;
        }
        
        $eventTxt = $this->context->getPack()->getName().' на '.$this->context->getSlot()->getName().' | '. $this->context->getPack()->getProject()->getName(false);
        
        $this->runtime->getEventProcessor()->add('🚀 Начата разливка: '.$eventTxt, EventConfig::EVENT_TYPE_DEPLOY_STATUS);
        $time = microtime(1);
        
        $deployFlow = (new CommandFlow())->getDeployFlow();
        
        foreach ($deployFlow as $command) {
            $this->runtime->startSection($command->getId(), $command->getHumanName());;
            
            $command->setRuntime($this->runtime);
            $command->setContext($this->context);
            $command->prepare();
            $command->run();
        }
    
        $this->runtime->getEventProcessor()->add('🍻 Разлито: '.$eventTxt.' ('.(round(microtime(1) - $time, 1)).'ceк)', EventConfig::EVENT_TYPE_DEPLOY_STATUS);
        $this->runtime->getEventProcessor()->add('Разливка релиза завершена', EventConfig::EVENT_TYPE_DEPLOY_END, [
            EventConfig::DATA_SLOT_NAME  => $this->context->getSlot()->getName(),
            EventConfig::DATA_BUILD_NAME => $this->context->getCheckpoint()->getName(),
        ]);
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_AND_ALL_DEPLOY;
    }
    
    public function getHumanName()
    {
        if ($this->context->getSlot()) {
            return 'Deploy to ' . $this->context->getSlot()->getName();
        }
        
        return 'Error: slot is not defined';
    }
    
    public function isPrimary(): bool
    {
        return true;
    }
}
