<?php

declare(strict_types=1);

namespace App\Services;

use App\Lib\Git\GitRepository;
use App\Lib\Git\Utils\StringHelper;
use App\Models\Sandbox;
use App\Models\Service;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SandboxRepositoryService extends GitRepositoryService
{
    protected function storage(): FilesystemAdapter
    {
        return Storage::disk('sandboxes');
    }
}
