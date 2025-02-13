<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sandbox;

use App\Actions\ResetSandboxReleaseBranchAction;
use App\Models\Sandbox;

class ResetSandboxReleaseBranchController
{
    public function __invoke(int $id)
    {
        $sandbox = Sandbox::find($id);

        $action = new ResetSandboxReleaseBranchAction();
        $action->execute($sandbox);

        return response()->view('sandboxes.action-results', [
            'header' => "{$sandbox->release->name} : Sandbox #{$sandbox->id}",
            'subheader' => "Reset release branch results",
            'sandbox' => $sandbox,
            'action' => 'Reset release branch',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
