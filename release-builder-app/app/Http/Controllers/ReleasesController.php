<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReleasesController extends Controller
{
    public function index()
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

        $releaseList = collect([$release1, $release2]);

        return response()->view('releases.index', [
            'releaseList' => $releaseList,
        ]);
    }
}
