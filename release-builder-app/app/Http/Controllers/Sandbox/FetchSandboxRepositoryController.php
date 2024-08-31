<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sandbox;

use App\Actions\FetchSandboxRepositoryAction;
use App\Models\Sandbox;

class FetchSandboxRepositoryController
{
    public function __invoke(int $id)
    {
        $sandbox = Sandbox::find($id);

        $action = new FetchSandboxRepositoryAction();
        $action->execute($sandbox);

        return response()->view('sandboxes.action-results', [
            'header' => $sandbox->release->name,
            'subheader' => "Fetch sandbox repository results",
            'sandbox' => $sandbox,
            'action' => 'Fetch sandbox repository',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
