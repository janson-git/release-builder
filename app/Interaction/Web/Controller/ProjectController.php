<?php


namespace Interaction\Web\Controller;


use Admin\App;
use Service\Project;

class ProjectController extends AuthControllerProto
{
    /**
     * @var
     */
    protected $node;
    
    /**
     * @var Project
     */
    private $project;
    
    private $projectId; 
    
    public function before()
    {
        $this->projectId = $this->p('id', $this->app->itemId);
        if ($this->projectId) {
            try {
                $this->project = Project::getById($this->projectId);
            } catch (\Exception $e) {
                $this->notFound($e->getMessage());
            }
        }
        
        parent::before();
    }
    
    public function slots () 
    {
        $this->setTitle(__('release_servers'));
        $this->setSubTitle($this->project->getName());
        
        $slots = $this->project->getSlotsPool()->loadProjectSlots()->validate()->getSlots();
        
        $this->response([
            'slots' => $slots,
            'id' => $this->projectId,
        ]);
    }

    public function removeBranch()
    {
        $id = $this->p('id', $this->app->itemId);
        $branchName = $this->p('branch');

        $project = Project::getById($id);
        $dirs = $project->getProjectRootDirs();

        $node = $project->getNode();
        $node->setRoot(dirname(getcwd()));
        $node->setDirs($dirs);
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();
        $sshPrivateKey = SSH_KEYS_DIR . '/' . App::i()->getAuth()->getUserLogin();
        $result = [];
        try {
            foreach ($node->getRepos() as $repo) {
                $start = microtime(1);
                if (in_array($branchName,  $repo->getRemoteBranches())) {
                    $repo->setSshKeyPath($sshPrivateKey);
//                    $repo->removeBranch('origin ' . $branchName);
                    $repo->push(['--delete', $branchName]);
                    $repo->fetch();
//                    $repo->removeBranch($branchName);
                }
                $result[$repo->getRepositoryPath()] = round(microtime(1) - $start, 4);
            }

            $ref = $this->app->request->getReferrer();
            if ($ref) {
                $this->app->redirect($ref);
            }

            $this->response([
                'pId'    => $this->project->getId(),
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            App::i()->log($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
            $this->response([
                'pId'    => $this->project->getId(),
                'result' => $result,
                'error' => $e->getMessage().' at '.$e->getFile().':'.$e->getLine(),
            ]);
        }
    }
}
