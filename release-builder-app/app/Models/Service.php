<?php

namespace App\Models;

use App\Services\GitRepositoriesService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * NOTE:
 * Service is name for repository entity. Repository is a some remote Git repo,
 * but service - is a wrapper on repo to represent it in Release Builder UI.
 * Release Builder's 'Services' have the name and repo url to make pull and push
 * actions.
 *
 * @property string $name
 * @property string $repository_url
 * @property string $status
 */
class Service extends Model
{
    use HasFactory;

    public const STATUS_FAILED = 'failed';
    public const STATUS_CLONED = 'cloned';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'repository_url',
        'status'
    ];

    /**
     * This method returns list of branches in MAIN cloned repository.
     * But every release has own sandbox clones of repositories
     */
    public function getBranches(): array
    {
        return app(GitRepositoriesService::class)->getServiceLocalBranches($this);
    }
}
