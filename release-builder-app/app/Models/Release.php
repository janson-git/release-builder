<?php

namespace App\Models;

use App\Casts\ReleaseBranchesCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * NOTE:
 * Release is a main entity in Release Builder.
 *
 * @property int $id
 * @property string $name
 * @property ReleaseBranches $branches
 * @property Carbon $delivery_date
 * @property int $created_by
 * @property string $filter
 * @property array $task_list
 *
 * @property-read array|Service[] $services
 * @property-read array|Sandbox[] $sandboxes
 * @property-read string $release_branch_name
 */
class Release extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'branches',
        'delivery_date',
        'filter',
        'task_list',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'branches' => ReleaseBranchesCast::class,
        'delivery_date' => 'date',
        'task_list' => 'array',
    ];

    public function sandboxes(): HasMany
    {
        return $this->hasMany(Sandbox::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'sandboxes');
    }

    /**
     * Wrapper to get release branch name
     * @return Attribute
     */
    protected function releaseBranchName(): Attribute
    {
        return Attribute::make(
            get: fn() => "release_{$this->id}_" . $this->created_at->format('Ymd_His'),
        );
    }
}
