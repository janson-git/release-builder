<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sandbox;

use App\Actions\PushSandboxReleaseBranchAction;
use App\Models\Sandbox;

class PushSandboxReleaseBranchController
{
    public function __invoke(int $id)
    {
        $sandbox = Sandbox::find($id);

        $action = new PushSandboxReleaseBranchAction();
        $action->execute($sandbox);

        return response()->view('sandboxes.action-results', [
            'header' => $sandbox->release->name . ' : Sandbox #' . $id,
            'subheader' => 'Push release branch result',
            'sandbox' => $sandbox,
            'action' => 'Push sandbox release branch',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
