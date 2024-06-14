<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Release;

class ReleasesController extends Controller
{
    public function index()
    {
        return response()->view('releases.index', [
            'releaseList' => Release::all(),
        ]);
    }

    public function show(int $id)
    {
        $release = Release::find($id);

        return response()->view('releases.show', [
            'release' => $release,
        ]);
    }
}
