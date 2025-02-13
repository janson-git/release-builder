<?php

declare(strict_types=1);

namespace App\Services\TaskTracker;

interface TaskTrackerInterface
{
    public function getTaskInfoByUrl(string $url): Task;

    /**
     * @param array $urls
     * @return array|Task[]
     */
    public function getTaskListInfoByUrls(array $urls): array;
}
