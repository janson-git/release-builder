<?php

declare(strict_types=1);

namespace App\Services\TaskTracker;

class Task
{
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly string $url,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
