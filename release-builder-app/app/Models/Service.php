<?php

namespace App\Models;

use App\Services\GitRepositoryLinkable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder ;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $directory
 * @property string $repository_name
 * @property string $repository_url
 * @property string $status
 * @property-read ServiceBoundRepository $repository
 * @property-read string $repo_type
 *
 * @method Builder notHttps Scope to filter services by 'https%' repository_url
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
        'repository_name',
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
            get: fn() => $this->getRepoType() . '/' . $this->attributes['directory'],
        );
    }

    protected function repoType(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getRepoType(),
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

    private function getRepoType(): string
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

    public function scopeNotHttps(Builder $q)
    {
        return $q->whereNot('repository_url', 'LIKE', 'http%');
    }
}
