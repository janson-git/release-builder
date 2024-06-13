<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NOTE:
 * Sandbox model just needed to link release and service
 * All sandbox jobs are manipulations with repository cloned directory
 * Each release has own sandboxes
 */
class Sandbox extends Model
{
    use HasFactory;

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
