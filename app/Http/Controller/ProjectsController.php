<?php

namespace App\Http\Controller;

use Admin\App;
use Commands\Command\Project\FetchProjectRepos;
use Commands\CommandContext;
use Psr\Http\Message\ResponseInterface;
use Service\Pack;
use Service\Project;
use Service\Node;
use Service\Data;

class ProjectsController extends AbstractController
{
    public function index(): ResponseInterface
    {
        $this->setTitle( __('projects'));
        ($hasRepos = !empty($this->app->directory()->allData()))
            && $this->setAction("/projects/create-new", __('create_project'));

        $projects = Data::scope(App::DATA_PROJECTS)->getAll();
        $packsData = Data::scope(App::DATA_PACKS)->getAll();

        $packsByProjects = [];

        foreach ($packsData as $id => $data) {
            $packsByProjects[$data['project']][$id] = Pack::getById($id);
        }

        return $this->view->render(
            'projects/index.blade.php',
            compact('projects', 'packsByProjects', 'hasRepos')
        );
    }

    public function show($id): ResponseInterface
    {
        $project = Project::getById($id);

        $this->setTitle('Project');
        $this->setSubTitle($project->getName());

        $project->getSlotsPool()->loadProjectSlots();

        $fetchCommand = new FetchProjectRepos();
        $fetchCommand->setContext((new CommandContext())->setProject($project));

        $this->setMainAction('/branches/create-pack/' . $id, __('create_pack'));
        $this->setAction(
            "/commands/apply?command={$fetchCommand->getId()}&context={$fetchCommand->getContext()->serialize()}",
            $fetchCommand->getHumanName()
        );

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
        $this->setTitle("Create Project");
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
        $project = Project::getById($id);

        $node = $project->getNode();
        $node->subLoad();
        $node->loadRepos();

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
