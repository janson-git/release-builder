<?php

namespace App\Http\Controller;

use Admin\App;
use Commands\Command\Project\FetchProjectRepos;
use Commands\CommandContext;
use Psr\Http\Message\ResponseInterface;
use Service\Project;
use Service\Node;
use Service\Data;

class ProjectsController extends AbstractController
{
    /**
     * @var
     */
    protected $node;
    
    public function index(): ResponseInterface
    {
        $this->setTitle( '<i class="fa-solid fa-folder-tree"></i>' . __('projects'));
        
        $projects = Data::scope(App::DATA_PROJECTS)->getAll();
        $packsData = Data::scope(App::DATA_PACKS)->getAll();

        $sets = [];
        foreach ($packsData as $id => $data) {
            $sets[$data['project']][$id] = $data;
        }

        return $this->view->render('projects/index.blade.php', [
            'projects' => $projects,
            'branchSets' => $sets,
            'hasRepos' => !empty($this->app->directory()->allData()),
        ]);
    }

    public function show($id): ResponseInterface
    {
        $project = Project::getById($id);

        $this->setTitle('<i class="fa-solid fa-folder-open"></i>' . $project->getName());
        $project->getSlotsPool()->loadProjectSlots();

        $fetchCommand = new FetchProjectRepos();
        $fetchCommand->setContext((new CommandContext())->setProject($project));

        return $this->view->render('projects/show.blade.php', [
            'project' => $project,
            'fetchCommand' => $fetchCommand,
            'id'        => $project->getId(),
            'setData'   => $project->getPaths(),
            'packs' => $project->getPacks(),
            'slots' => $project->getSlotsPool()->getSlots(),
        ]);
    }

    public function showCreateForm(): ResponseInterface
    {
        // SHOW NAVIGATOR FORM TO CREATE PROJECT
        $this->setTitle(__('repositories_navigation'));
        $pack = $this->p('pack');

        $node = new Node();

        if ($pack) {
            $node->setRoot($node->getRoot() . $pack, false);
        } else {
            $pack = $this->p('dirScan');
        }

        $node->setDepth(0);

        $dirs   = $this->p('dirs', []);
        $passed = $dirs;

        try {
            if ($dirs) {
                $node->setDirs($dirs);
                $node->subLoad();
                $node->loadRepos();
            } else {
                $node->loadDirs();
                $node->loadRepos();
            }

            $node->loadBranches();
        } catch (\Exception $e) {

        }

        return $this->view->render('projects/createProjectForm.blade.php', [
            'dirs' => $node->getDirs(),
            'node'       => $node,
            'passedDirs' => $passed,
            'showScan'   => $pack,
            'msg' => isset($e) ? $e->getMessage() : '',
        ]);
    }

    public function store(): ResponseInterface
    {
        $saveDirs = $this->p('saveDirs');
        $dirs = explode(',', $saveDirs);

        $project = new Project();
        $project->setProjectRootDirs($dirs)
            ->save();

        return $this->response->withRedirect("/projects/{$project->getId()}");
    }

    /**
     * Used on 'Fetch repositories and return' button click
     */
    public function fetch(int $id): ResponseInterface
    {
        $projectsDirs = Data::scope(App::DATA_PROJECTS)->reload()->getAll();
        $dirs         = $projectsDirs[$id];

        $node = new Node();
        $node->setDirs($dirs);
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();

        $result = [];

        foreach ($node->getRepos() as $repo) {
            $start = microtime(1);
            $repo->fetch();
            $result[$repo->getRepositoryPath()] = round(microtime(1) - $start, 4);
        }

        if ($this->p('return')) {
            return $this->response->withRedirect($this->request->getServerParam('HTTP_REFERER'));
        }

        return $this->view->render('projects/fetch.blade.php', [
            'pId'    => $id,
            'result' => $result,
        ]);
    }
}
