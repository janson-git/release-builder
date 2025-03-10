<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\FetchReleaseRepositoriesAction;
use App\Models\Release;
use Illuminate\Http\Request;

class FetchRepositoriesController
{
    public function __invoke(int $id, Request $request)
    {
        $release = Release::find($id);

        $action = new FetchReleaseRepositoriesAction();
        $action->execute($release);

        if ($request->has('return')) {
            return redirect()->back();
        }

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Fetch release repositories results",
            'release' => $release,
            'action' => 'Fetch release repositories',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
