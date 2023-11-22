<?php

namespace Service;

use Commands\Command\CommandProto;

/**
 * Checkpoint mapped to branch in repository
 */
class Checkpoint
{
    protected Pack $pack;
    
    /** @var CommandProto[] */
    protected array $commands = [];
    
    /**
     * Branch name used as checkpoint ID
     */
    protected string $id;

    // TODO: convert it to typed object
    protected array $details = [];

    public function __construct(Pack $pack, string $id, array $details = [])
    {
        $this->pack = $pack;
        $this->id = $id;
        $this->details = $details;
    }
    
    public function getName(): string
    {
        return $this->id; 
    }

    public function getDetails(): array
    {
        return $this->details;
    }
    
    public function getPack(): Pack
    {
        return $this->pack;
    }

    /**
     * @return CommandProto[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
    
    /**
     * @param CommandProto[] $commands
     */
    public function setCommands(array $commands): self
    {
        foreach ($commands as $command) {
            $command->getContext()->setCheckpoint($this);
        }
        
        $this->commands = $commands;

        return $this;
    }
    
    public function getBuildPath(): string
    {
        $projectName = $this->pack->getProject()->getNameQuoted();
        $checkpointName = $this->id;
        
        return "builds/{$projectName}/{$checkpointName}";
    }
}
