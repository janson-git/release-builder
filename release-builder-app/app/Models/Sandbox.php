<?php

namespace App\Models;

use App\Services\GitRepositoryLinkable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * NOTE:
 * Sandbox model just needed to link release and service
 * All sandbox jobs are manipulations with repository cloned directory
 * Each release has own sandboxes
 *
 * @property string $status
 */
class Sandbox extends Model implements GitRepositoryLinkable
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_OK = 'ok';
    public const STATUS_HAS_ERRORS = 'errors';
    public const STATUS_CONFLICT = 'conflict';

    public $fillable = [
        'status'
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getRepositoryUrl(): string
    {
        return $this->service->repository_url;
    }

    public function getRepositoryDirectoryName(): string
    {
        return $this->service->directory;
    }

    public function getRepositoryPath(): string
    {
        return Storage::disk('sandboxes')->path($this->service->directory);
    }

    public function markAsConflicted(): void
    {
        $this->update(['status' => self::STATUS_CONFLICT]);
    }

    public function markAsHasErrors(): void
    {
        $this->update(['status' => self::STATUS_HAS_ERRORS]);
    }

    public function markAsGood(): void
    {
        $this->update(['status' => self::STATUS_OK]);
    }
}
