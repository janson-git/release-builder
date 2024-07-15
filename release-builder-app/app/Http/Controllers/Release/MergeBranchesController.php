<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\MergeReleaseBranchesAction;
use App\Models\Release;

class MergeBranchesController
{
    public function __invoke(int $id)
    {
        $release = Release::find($id);

        $action = new MergeReleaseBranchesAction();
        $action->execute($release);

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Merge branches results",
            'release' => $release,
            'action' => 'Merge Branches',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
