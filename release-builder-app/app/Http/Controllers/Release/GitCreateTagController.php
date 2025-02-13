<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Actions\GitCreateTagAction;
use App\Models\Release;
use Illuminate\Http\Request;

class GitCreateTagController
{
    public function __invoke(int $id, Request $request)
    {
        $request->validate([
            'tag' => 'required|string',
        ]);
        $tag = $request->get('tag');

        $release = Release::find($id);

        $action = new GitCreateTagAction($tag);
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
