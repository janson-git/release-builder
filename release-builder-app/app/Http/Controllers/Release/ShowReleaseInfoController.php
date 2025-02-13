<?php

declare(strict_types=1);

namespace App\Http\Controllers\Release;

use App\Models\Release;
use App\Services\TaskTracker\TaskTrackerInterface;

class ShowReleaseInfoController
{
    public function __invoke(int $id)
    {
        /** @var Release $release */
        $release = Release::find($id);

        /** @var TaskTrackerInterface $taskTracker */
        $taskTracker = app()->get(TaskTrackerInterface::class);

        $tasks = $taskTracker->getTaskListInfoByUrls($release->task_list);

        $releaseInfo = view('releases.release-info-template', [
            'release' => $release,
            'tasks' => $tasks
        ]);

        return response()->view('releases.release-info', [
            'header' => $release->name,
            'subheader' => "Release brief",
            'release' => $release,
            'releaseInfo' => $releaseInfo,
        ]);
    }
}
