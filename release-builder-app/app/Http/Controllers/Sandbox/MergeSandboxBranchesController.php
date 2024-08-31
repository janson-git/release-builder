<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sandbox;

use App\Actions\MergeSandboxBranchesAction;
use App\Models\Sandbox;

class MergeSandboxBranchesController
{
    public function __invoke(int $id)
    {
        $sandbox = Sandbox::find($id);

        $action = new MergeSandboxBranchesAction();
        $action->execute($sandbox);

        return response()->view('sandboxes.action-results', [
            'header' => $sandbox->release->name . ' : Sandbox #' . $id,
            'subheader' => "Merge branches results",
            'sandbox' => $sandbox,
            'action' => 'Merge Branches',
            'actionLog' => $action->getActionLog(),
            'errorLog' => $action->getActionErrorLog(),
        ]);
    }
}
