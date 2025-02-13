<?php

namespace App\Services\TaskTracker\Api;

use App\Services\TaskTracker\Task;
use App\Services\TaskTracker\TaskTrackerInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class Mayven extends Rest implements TaskTrackerInterface
{
    protected Client $client;

    private array $projectsCache = [];

    protected function baseUri(): string
    {
        return config('tasktracker.services.mayven.api_url');
    }

    public function headers(): array
    {
        return [
            'Authorization' => config('tasktracker.services.mayven.auth'),
            'Accept' => 'application/json',
        ];
    }

    public function getUserId(): ?int
    {
        return Cache::rememberForever('mayven_user_id', function() {
            $res = $this->get("/api/hydrate");
            $data = json_decode($res->getBody()->getContents());
            return $data->data->me->data->id;
        });
    }

    public function getTaskInfoByUrl(string $url, ?int $projectId = null): Task
    {
        [$link, $projectSlug, $idInProject] = $this->extractLinkAndProjectSlugAndTaskId($url);

        if ($projectId === null) {
            $projectId = $this->getProjectIdBySlug($projectSlug);
        }

        // get tasks info in project
        $res = $this->get("/api/projects/{$projectId}/todos/{$idInProject}");

        $data = json_decode($res->getBody()->getContents(), true);
        $info = $data['data'];
        return new Task(
            $idInProject,
            $info['title'],
            $link
        );
    }

    /**
     * @param array $urls
     * @return array|Task[]
     */
    public function getTaskListInfoByUrls(array $urls): array
    {
        $groupedByProjects = [];
        foreach ($urls as $url) {
            [$link, $projectSlug, $idInProject] = $this->extractLinkAndProjectSlugAndTaskId($url);

            $groupedByProjects[$projectSlug][] = [
                'link' => $link,
                'id' => $idInProject
            ];
        }

        $tasks = [];
        foreach ($groupedByProjects as $projectSlug => $items) {
            $projectId = $this->getProjectIdBySlug($projectSlug);

            // TODO: ok, right now we don't have filter for id_in_project array
            // TODO: let get it iteratively step by step
            foreach ($items as $item) {
                $tasks[$item['link']] = $this->getTaskInfoByUrl($item['link'], $projectId);
            }
        }

        return $tasks;
    }

    /**
     * @param string $url
     * @return array
     * [ <LINK>, <PROJECT_SLUG>, <TASK_ID_IN_PROJECT> ]
     */
    private function extractLinkAndProjectSlugAndTaskId(string $url): array
    {
        $matches = [];
        preg_match('|https?.*/p/([^/]*)/tasks[^#]*?#task-(\d+)-\w+|', $url, $matches);

        return [$matches[0], $matches[1], $matches[2]];
    }

    private function getProjectIdBySlug(string $slug): ?int
    {
        if (array_key_exists($slug, $this->projectsCache)) {
            return $this->projectsCache[$slug];
        }

        $res = $this->get('/api/projects', ['query' => ['slug' => $slug]]);

        $data = json_decode($res->getBody()->getContents(), true);
        $this->projectsCache[$slug] = $data['data'][0]['id'];

        return $this->projectsCache[$slug];
    }
}
