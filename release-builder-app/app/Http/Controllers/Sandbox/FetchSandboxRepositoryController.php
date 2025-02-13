<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sandbox;

use App\Actions\FetchSandboxRepositoryAction;
use App\Models\Sandbox;
use Illuminate\Http\Request;

class FetchSandboxRepositoryController
{
    public function __invoke(int $id, Request $request)
    {
        $sandbox = Sandbox::find($id);

        $action = new FetchSandboxRepositoryAction();
        $action->execute($sandbox);

        if ($request->has('return')) {
            return redirect()->back();
        }

        return response()->view('sandboxes.action-results', [
            'header' => $sandbox->release->name,
            'subheader' => "Fetch sandbox repository results",
            'sandbox' => $sandbox,
            'action' => 'Fetch sandbox repository',
            'actionLog' => $action->getActionLog(),
        ]);
    }
}
