<?php

namespace App\Http\Controller;

use Slim\Http\Response;
use Slim\Http\StatusCode;

class GitController extends AbstractController
{
    public function index(): Response
    {
        $this->app->view()->setHeader(__('deploy'));

        return $this->view->render('git/index.blade.php', [
            'list' => $this->app->directory()->allData(),
        ]);
    }

    public function update(): Response
    {
        $dir = $this->request->getParam('dir');

        return $this->app->json([
            'data' => $this->app->directory()->update($dir)
        ]);
    }

    public function checkout(): Response
    {
        $dir = $this->request->getParam('dir');
        $branch = $this->request->getParam('branch', '');

        return $this->app->json([
            'data' => $this->app->directory()->checkout($dir, $branch)
        ]);
    }

    public function reset(): Response
    {
        $dir = $this->request->getParam('dir');

        return $this->app->json([
            'data' => $this->app->directory()
                ->fix($dir, $this->request->getParam('doClean', false))
        ]);
    }

    public function showAddRepositoryForm(): Response
    {
        $this->setTitle(__('deploy'));
        $this->setSubTitle(__('add_repository'));

        return $this->view->render('git/addRepositoryForm.blade.php');
    }

    public function addRepository(): Response
    {
        // SSH link like: git@github.com:janson-git/deploy.git
        // HTTPS url like: https://github.com/janson-git/deploy.git

        $repoPath = $this->p('repository_path');
        $repoPath = preg_replace('#[^a-zA-Z0-9:@./\-]#', '', $repoPath);

        $repoNameFull = mb_substr($repoPath, strrpos($repoPath, '/') + 1);
        $dirName = str_replace('.git', '', $repoNameFull);

        if (str_starts_with($repoPath, 'git@github') && !$this->app->getAuth()->isSshKeyExists()) {
            return $this->app->json([
                    'error' => 'You should add SSH key in your profile to use SSH repository links',
                ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $output = $this->app->directory()->cloneRepository($repoPath, $dirName);
        } catch (\Exception $e) {
            $output = $e->getMessage();
            return $this->app->json(['error' => $output], 500);
        }

        return $this->app->json(['data' => $output]);
    }
}
