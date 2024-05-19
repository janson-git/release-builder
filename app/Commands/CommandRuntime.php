<?php

namespace Commands;

use Service\Event\TelegramBot;
use Service\Events;

class CommandRuntime implements \ArrayAccess
{
    private array $data = [];
    
    private array $logs = [];
    private ?string $currentSection = null;
    private ?float $currentSectionStartTime = null;
    private array $sectionNames = [];
    private int $coreSection = 0;
    private array $errors      = [];
    private array $exceptions  = [];

    private Events $eventProcessor;

    public function __construct()
    {
        $this->eventProcessor = new Events();
        $this->eventProcessor->addProvider(new TelegramBot());
    }
    
    public function startSection(string $id, string $name): void
    {
        if ($this->currentSection !== $id && $this->currentSectionStartTime && $this->logs[$this->currentSection]) {
            $time = round(microtime(1) - $this->currentSectionStartTime, 4);
            $this->logs[$this->currentSection]['time'] = $time;
        }
        
        $this->currentSection = $id;
        $this->currentSectionStartTime = microtime(1);
        $this->sectionNames[$id] = $name;
        if (!isset($this->logs[$this->currentSection])) {
            $this->logs[$this->currentSection] = [];
        }
    }
    
    private function _checkSection(): void
    {
        if (!$this->currentSection) {
            $this->startSection('core_' . (++$this->coreSection), 'Логи ядра #' . $this->coreSection);
        }   
    }
    
    public function log($data, $key = null): void
    {
        $this->_checkSection();
        
        if ($key === null) {
            $this->logs[$this->currentSection][] = $data;
        } else {
            if (isset($this->logs[$this->currentSection][$key])) {

                // check if current key is array, if not convert to array
                if(!is_array($this->logs[$this->currentSection][$key])) {
                    $this->logs[$this->currentSection][$key] = [
                        $this->logs[$this->currentSection][$key]
                    ];
                }

                // check incoming data type
                if (!is_array($data)) // not an array
                {
                    $this->logs[$this->currentSection][$key][] = $data;
                }
                else if (array_keys($data) !== range(0, count($data) - 1)) // associative array
                {
                    foreach ($data as $dataKey => $dataItem) {
                        $this->logs[$this->currentSection][$key][$dataKey] = $dataItem;
                    }
                }
                else // not associative array
                {
                    foreach ($data as $dataItem) {
                        $this->logs[$this->currentSection][$key][] = $dataItem;
                    }
                }
            } else {
                $this->logs[$this->currentSection][$key] = $data;    
            }
        }
    }
    
    public function error($data): void
    {
        $this->_checkSection();
        
        if (!isset($this->errors[$this->currentSection])) {
            $this->errors[$this->currentSection] = [];
        }
        
        $this->errors[$this->currentSection][] = $data;
        $this->log($data);
    }
    
    public function exception(\Exception $exception)
    {
        $this->_checkSection();
        
        if (!isset($this->exceptions[$this->currentSection])) {
            $this->exceptions[$this->currentSection] = [];
        }
        
        $this->exceptions[$this->currentSection][] = $exception;
        $this->log($exception->getMessage());
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
 
    public function getSectionName(string $id): string
    {
        return $this->sectionNames[$id];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }
    
    /**
     * @param      $key
     * @param null $default
     *
     * @return array
     */
    public function getData($key, $default = null): array
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * @param       $key
     * @param array $data
     */
    public function setData($key, $data)
    {
        $this->data[$key] = $data;
    }
    
    public function offsetExists($offset)
    {
        return isset($this->data[$this->currentSection][$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->data[$this->currentSection][$offset]) ? $this->data[$this->currentSection][$offset] : null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->log($value, $offset);
        return true;
    }
    
    public function offsetUnset($offset)
    {
        unset($this->data[$this->currentSection][$offset]);
    }

    public function getEventProcessor(): Events
    {
        return $this->eventProcessor;
    }
}
