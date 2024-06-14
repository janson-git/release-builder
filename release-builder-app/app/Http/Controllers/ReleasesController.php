<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ReleaseService;

class ReleasesController extends Controller
{
    public function index()
    {
        $releaseService = app(ReleaseService::class);

        return response()->view('releases.index', [
            'releaseList' => $releaseService->getReleases(),
        ]);
    }

    public function show(int $id)
    {
        $release = app(ReleaseService::class)->getRelease($id);

        return response()->view('releases.show', [
            'release' => $release,
        ]);
    }
}
