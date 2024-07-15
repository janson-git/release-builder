<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\SearchConflictBranchesInReleaseAction;
use App\Models\Release;

class SearchConflictBranchesController
{
    public function __invoke(int $id)
    {
        $release = Release::find($id);

        $action = new SearchConflictBranchesInReleaseAction();
        $action->execute($release);

        return response()->view('releases.action-results', [
            'header' => $release->name,
            'subheader' => "Search conflicts results",
            'release' => $release,
            'action' => 'Search Conflicts',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
