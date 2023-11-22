<?php

namespace Commands\Command;

use Commands\CommandRuntime;
use Commands\CommandContext;
use Service\Slot\SlotProto;

abstract class CommandProto
{
    /** @var CommandContext */
    protected $context;
    
    /** @var string */
    protected $allRoot;
    
    /** @var array */
    protected $errors = [];
    
    /** @var CommandRuntime */
    protected $runtime;
    
    protected $data;
    
    public function __construct()
    {
        $this->allRoot = dirname(getcwd());
        $this->allRoot .= '/builds';
    }
    
    public function prepare()
    {
        // do nothing by default
    }

    abstract public function run();
    abstract public function getId();
    abstract public function getHumanName();
    
    public function getSlot (): ?SlotProto
    {
        return $this->context->getSlot();
    }
    
    public function getRuntime()
    {
        return $this->runtime;
    }
    
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function isPrimary(): bool
    {
        return false;
    }
    
    public function setRuntime(CommandRuntime $runtime): void
    {
        $this->runtime = $runtime;
    }
    
    public function getContext(): CommandContext
    {
        if (!$this->context) {
            $this->context = new CommandContext();
        }
        
        return $this->context;
    }
    
    public function setContext(CommandContext $context): void
    {
        $this->context = $context;
    }
    
    public function getLink(): string
    {
        return '/commands/apply?command='.$this->getId().'&context='.$this->getContext()->serialize();
    }

    public function isConfirmRequired(): bool
    {
        return false;
    }

    /**
     * Ask user questions and got answer in `userData` context field
     * @see CommandContext::USER_CONTEXT
     * @return array
     */
    public function isQuestion(): array
    {
        return [];
    }

    public function hasQuestion(): bool
    {
        return !empty($this->isQuestion());
    }

    /**
     * For indicate danger actions in interface
     * @return bool
     */
    public function isDanger(): bool
    {
        return false;
    }
}