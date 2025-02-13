<?php

declare(strict_types=1);

namespace App\Services;

interface GitRepositoryLinkable
{
    public function getRepositoryUrl(): string;
    public function getRepositoryDirectoryName(): string;
    public function getRepositoryPath(): string;
}
