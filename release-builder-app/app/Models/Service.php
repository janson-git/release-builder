<?php

namespace App\Models;

use App\Services\GitRepositoryLinkable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $directory
 * @property string $repository_url
 * @property string $status
 * @property-read ServiceBoundRepository $repository
 */
class Service extends Model implements GitRepositoryLinkable
{
    use HasFactory;

    public const STATUS_FAILED = 'failed';
    public const STATUS_CLONED = 'cloned';

    public const TYPE_SSH = 'ssh';
    public const TYPE_HTTPS = 'https';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'directory',
        'repository_url',
        'status'
    ];

    /**
     * Wrapper to access GitRepository bound to current service
     * @return Attribute
     */
    protected function repository(): Attribute
    {
        return Attribute::make(
            get: fn() => app(ServiceBoundRepository::class, [
                'service' => $this
            ]),
        );
    }

    protected function directory(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->repoType() . '/' . $this->attributes['directory'],
        );
    }

    public function releases(): BelongsToMany
    {
        return $this->belongsToMany(Release::class);
    }

    public function getRepositoryUrl(): string
    {
        return $this->repository_url;
    }

    public function getRepositoryDirectoryName(): string
    {
        return $this->directory;
    }

    private function repoType(): string
    {
        if (str_starts_with($this->repository_url, 'git@')) {
            return self::TYPE_SSH;
        }
        return self::TYPE_HTTPS;
    }

    public function getRepositoryPath(): string
    {
        return Storage::disk('repositories')->path($this->directory);
    }
}
