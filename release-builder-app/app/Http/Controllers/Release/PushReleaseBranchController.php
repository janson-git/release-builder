<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\PushReleaseBranchAction;
use App\Models\Release;

class PushReleaseBranchController
{
    public function __invoke(int $id)
    {
        $release = Release::find($id);

        $action = new PushReleaseBranchAction();
        $action->execute($release);

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Git Create Tag results",
            'release' => $release,
            'action' => 'Git Create Tag',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
