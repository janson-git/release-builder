<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\ResetReleaseBranchAction;
use App\Models\Release;

class ResetReleaseBranchController
{
    public function __invoke(int $id)
    {
        $release = Release::find($id);

        $action = new ResetReleaseBranchAction();
        $action->execute($release);

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Reset release branch results",
            'release' => $release,
            'action' => 'Reset release branch',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
