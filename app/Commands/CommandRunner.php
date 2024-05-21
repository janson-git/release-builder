<?php

namespace Commands;

use Exception;
use Commands\Command\CommandProto;
use Exceptions\UnauthorizedException;
use Service\Util\Lock;

class CommandRunner
{
    /** @var CommandProto[] */
    private array $commands = [];
    
    /** @var CommandContext */
    private $context;
    
    protected array $commandIdsToRun = [];

    private CommandRuntime $runtime;


    public function __construct()
    {
        $this->runtime = new CommandRuntime();
    }
    
    public function run () 
    {
        $this->context->set(CommandConfig::GLOBAL_WORK_DIR, dirname(getcwd()));
        
        foreach ($this->commandIdsToRun as $commandId) {
            $command = CommandConfig::getCommand($commandId);
            $this->runCommand($command);
        }
    }
    
    public function runCommand (CommandProto $command): bool
    {
        try {
            $command->setContext($this->context);
            $command->setRuntime($this->runtime);
            
            $this->runtime->startSection($command->getId(), $command->getHumanName());
            
            $pack = $this->context->getPack();

            if (!$command->isAuthorizedForCurrentUser() ) {
                throw new UnauthorizedException('You are not owner of package');
            }

            if ($pack) {
                $project = $pack->getProject();
                $lock = new Lock('pack_'.$project->getNameQuoted().'_'.$pack->getName(), $command->getHumanName());
                
                if (!$lock->get()) {
                    $this->runtime->log('Pack locked by @'.$lock->getLockData(Lock::OWNER).' for "'
                        .$lock->getLockData(Lock::REASON).'". Lock surely expired after '
                        .($lock->getLockData(Lock::EXPIRE) - time()).'sec.', 'lock');
                    
                    return false;
                }
            }
        
            $command->prepare();
            $command->run();
        
        } catch (Exception $e) {
            $this->runtime->exception($e);
            return false;
        }
        
        isset($lock) && $lock->release();
        
        return true;
    }
    
    /**
     * @return Command\CommandProto[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
    
    /**
     * @param Command\CommandProto[] $commands
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

    public function getCommandIdsToRun(): array
    {
        return $this->commandIdsToRun;
    }
    
    /**
     * @param string[] $commandsOrder
     */
    public function setCommandIdsToRun(array $commandsOrder): void
    {
        $this->commandIdsToRun = $commandsOrder;
    }

    public function getRuntime(): CommandRuntime
    {
        return $this->runtime;
    }
    
    /**
     * @return CommandContext
     */
    public function getContext(): CommandContext
    {
        return $this->context;
    }

    public function setContext(CommandContext $context): void
    {
        $this->context = $context;
    }
    
}
