<?php

namespace Commands;

use Service\Checkpoint;
use Service\Pack;
use Service\Project;
use Service\Slot\SlotProto;
use Service\SlotFactory;
use Service\SlotsPool;

class CommandContext
{
    const DATA_CHECKPOINT = 'checkpoint';
    const DATA_SLOT = 'slot';
    const DATA_PACK = 'pack';
    const DATA_PROJECT = 'project';
    const USER_CONTEXT = '_userContext';

    /**
     * @var Checkpoint
     */
    protected $checkpoint;
    
    /** @var Pack|null */
    protected $pack;
    
    /**
     * @var SlotProto
     */
    protected $slot;
    
    /**
     * @var Project
     */
    protected $project;
    
    private $data = [];
    
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    public function set($key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    public function serialize()
    {
        return base64_encode(json_encode($this->getState()));
    }
    
    public function getState()
    {
        $data = [];
        
        if ($this->pack) {
            $data[self::DATA_PACK] = $this->pack->getId();
        }
        
        if ($this->checkpoint) {
            $data[self::DATA_CHECKPOINT] = $this->checkpoint->getName();
        }
        
        if ($this->slot) {
            $data[self::DATA_SLOT] = $this->slot->getId();
        }
        
        if ($this->project) {
            $data[self::DATA_PROJECT] = $this->project->getId();
        } elseif($this->pack) {
            $data[self::DATA_PROJECT] = $this->pack->getProject()->getId();
        }
        
        return $data;
    }
    
    public function deserialize($string)
    {
        $data = json_decode(base64_decode($string), 1);
        $this->bind($data);
    }
    
    public function bind($data)
    {
        if (isset($data[self::DATA_PACK])) {
            $this->pack = Pack::getById($data[self::DATA_PACK]);
        }
        
        if (isset($data[self::DATA_CHECKPOINT]) && $this->pack) {
            $this->checkpoint = new Checkpoint($this->pack, $data[self::DATA_CHECKPOINT]);
            $this->checkpoint->setCommands($this->pack->getCheckpointCommands()); // todo remove
        }
        
        if (isset($data[self::DATA_SLOT])) {
            $this->slot = SlotFactory::getSlot($data[self::DATA_SLOT]);
        }
        
        if (isset($data[self::DATA_PROJECT])) {
            $this->project = Project::getById($data[self::DATA_PROJECT]);
        }
    }
    
    public function getPack(): ?Pack
    {
        return $this->pack;
    }

    public function setPack(Pack $pack): self
    {
        $this->pack = $pack;
        return $this;
    }
    
    public function getCheckpoint(): ?Checkpoint
    {
        return $this->checkpoint;
    }
    
    public function setCheckpoint(Checkpoint $checkpoint): self
    {
        $this->checkpoint = $checkpoint;
        return $this;
    }

    public function getSlot(): ?SlotProto
    {
        return $this->slot;
    }
    
    public function setSlot(SlotProto $slot): self
    {
        $this->slot = $slot;
        return $this;
    }
    
    public function getProject(): ?Project
    {
        return $this->project;
    }
    
    public function setProject(Project $project): self
    {
        $this->project = $project;
        return $this;
    }
}
