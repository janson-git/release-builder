<?php

declare(strict_types=1);

namespace App\Actions;

abstract class AbstractAction
{
    protected const ACTION_NAME = '';

    protected array $log = [];

    public function getActionLog(): array
    {
        if (static::ACTION_NAME) {
            return [static::ACTION_NAME => $this->log];
        }
        return [$this->log];
    }

    protected function log($data, ?string $key = null): void
    {
        if ($key === null) {
            $this->log[] = $data;
        } else {
            $this->log[$key] = $data;
        }
    }
}
