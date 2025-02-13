<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sandbox;

use App\Actions\SearchSandboxConflictBranchesAction;
use App\Models\Sandbox;

class SearchSandboxConflictBranchesController
{
    public function __invoke(int $id)
    {
        $sandbox = Sandbox::find($id);

        $action = new SearchSandboxConflictBranchesAction();
        $action->execute($sandbox);

        return response()->view('sandboxes.action-results', [
            'header' => $sandbox->release->name . ' : Sandbox #' . $sandbox->id,
            'subheader' => "Search conflicts results",
            'sandbox' => $sandbox,
            'action' => 'Search Conflicts',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
