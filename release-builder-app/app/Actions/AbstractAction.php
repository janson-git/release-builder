<?php

declare(strict_types=1);

namespace App\Actions;

abstract class AbstractAction
{
    protected const ACTION_NAME = '';

    protected array $log = [];
    protected array $errorLog = [];

    public function getActionLog(): array
    {
        if (static::ACTION_NAME) {
            return [static::ACTION_NAME => $this->log];
        }
        return [$this->log];
    }

    public function getActionErrorLog(): array
    {
        if (static::ACTION_NAME) {
            return [static::ACTION_NAME => $this->errorLog];
        }
        return [$this->errorLog];
    }

    protected function log($data, ?string $key = null): void
    {
        if ($key === null) {
            $this->log[] = $data;
        } else {
            if (isset($this->log[$key])) {
                if (!is_array($this->log[$key])) {
                    $this->log[$key] = [ $this->log[$key] ];
                }

                $this->log[$key][] = $data;
            } else {
                $this->log[$key] = $data;
            }
        }
    }

    protected function logError($data, ?string $key = null): void
    {
        if ($key === null) {
            $this->errorLog[] = $data;
        } else {
            if (isset($this->errorLog[$key])) {
                if (!is_array($this->errorLog[$key])) {
                    $this->errorLog[$key] = [ $this->errorLog[$key] ];
                }

                // if we already have the same error - just skip it
                $errorJson = json_encode($data);
                $hasError = false;
                foreach ($this->errorLog[$key] as $error) {
                    $error = json_encode($error);
                    if ($error === $errorJson) {
                        $hasError = true;
                        break;
                    }
                }
                if (!$hasError) {
                    $this->errorLog[$key][] = $data;
                }
            } else {
                $this->errorLog[$key][] = $data;
            }
        }
    }
}
