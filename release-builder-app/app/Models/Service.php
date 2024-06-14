<?php

namespace App\Models;

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
 * @property-read array $branches
 */
class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'repository_url',
    ];

    /**
     * This method returns list of branches in MAIN cloned repository.
     * But every release has own sandbox clones of repositories
     */
    public function getBranchesAttribute(): array
    {
        // TODO: should scan repository dir for branches list every time
        return [];
    }
}
