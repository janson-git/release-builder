<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\FetchReleaseSandboxesAction;
use App\Models\Release;

class FetchRepositoriesController
{
    public function __invoke(int $id)
    {
        $release = Release::find($id);

        $action = new FetchReleaseSandboxesAction();
        $action->execute($release);

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Fetch release repositories results",
            'release' => $release,
            'action' => 'Fetch release repositories',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
