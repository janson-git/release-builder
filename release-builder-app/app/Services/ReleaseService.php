<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReleaseService
{
    public function __construct(
        private Collection $releases
    )
    {
        // FIXME: STUB RELEASE FOR UI DRAFT
        $release1 = new Release([
            'name' => 'Stub release 1',
            'branches' => ['task-1', 'task-3', 'task-4'],
            'delivery_date' => Carbon::now()->addDays(5),
        ]);
        $release1->id = 1;

        $release2 = new Release([
            'name' => 'Stub release 2',
            'branches' => ['task-2', 'task-5', 'task-6'],
            'delivery_date' => Carbon::now()->addDays(10),
        ]);
        $release2->id = 2;

        $this->releases = collect([$release1, $release2])->keyBy('id');
    }

    public function getReleases(): Collection
    {
        return $this->releases;
    }

    public function getRelease(int $id): Release
    {
        return $this->releases->get($id);
    }
}
