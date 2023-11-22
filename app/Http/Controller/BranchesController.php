<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Psr\Http\Message\ResponseInterface;
use Service\Node;
use Service\Pack;
use Service\Project;

class BranchesController  extends AbstractController
{
    const ACTION_PACK_CREATE          = 'create';
    const ACTION_PACK_ADD_BRANCH      = 'add';
    const ACTION_PACK_CHANGE_BRANCHES = 'change';
    const ACTION_PACK_FORK            = 'fork';

    /**  @var Node */
    private $node;

    /** @var Project */
    private $project;

    /** @var Pack */
    private $pack;

    /** @var array */
    private $packBranches = [];

    private function prepare(int $projectId, ?int $packId = null): void
    {
        $this->project = Project::getById($projectId);

        $node = $this->project->getNode();
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();

        $this->node = $node;
        // pack ID could be missed if it is action 'CREATE PACK'
        // in other actions: add/remove branches, fork pack - Pack ID should be presents
        if ($packId) {
            $this->pack = Pack::getById($packId);
            $this->packBranches = $this->pack->getBranches();
        }
    }

    public function addBranches(int $projectId, int $packId)
    {
        $this->prepare($projectId, $packId);

        return $this->renderList([
            'title' => __('add_branches'),
            'action' => self::ACTION_PACK_ADD_BRANCH
        ]);
    }

    public function removeBranches(int $projectId, int $packId)
    {
        $this->prepare($projectId, $packId);

        return $this->renderList([
            'title' => __('remove_branches_from_pack'),
            'selected' => array_flip($this->pack->getBranches()),
            'action' => self::ACTION_PACK_CHANGE_BRANCHES
        ]);
    }

    public function forkPack(int $projectId, int $packId)
    {
        $this->prepare($projectId, $packId);

        $this->renderList([
            'title' => __('fork_pack'),
            'selected' => array_flip($this->pack->getBranches()),
            'action'   => self::ACTION_PACK_FORK
        ]);
    }

    public function createPack(int $projectId)
    {
        $this->prepare($projectId);

        $this->renderList([
            'title' => __('pack_creation'),
            'action' => self::ACTION_PACK_CREATE
        ]);
    }

    private function renderList($data): ResponseInterface
    {
        // get list of all branches in all repos of project:
        // [branch_name] => [ repo_name => repo_name, repo_name2 => repo_name2 ]
        $branches = $this->project->getNode()->getRepoDirsByBranches();

        $packReposByBranches = $this->node->getToMasterStatus($this->packBranches);

        if ($this->pack) {
            $this->setTitle(__('pack') . " '{$this->pack->getName()}'");
        } else {
            $this->setTitle($this->project->getName());
        }

        return $this->view->render('branches/list.blade.php', $data + [
                'project'  => $this->project,
                'pack'     => $this->pack ?? null,
                'selected' => [],
                'packBranches' => $this->packBranches,
                'branches' => $branches,
                'branchesData' => $packReposByBranches
            ]);
    }

    /**
     * Сохранение выбора
     */
    public function savePack(int $projectId)
    {
        $action = $this->p('action');
        $packId = $this->p('packId');
        $branches  = $this->p('branches', []);

        $this->prepare($projectId, $packId ? (int) $packId : null);

        if ($action === self::ACTION_PACK_CREATE || $action === self::ACTION_PACK_FORK) {
            $pack = $this->_createPack($branches);
        } elseif ($action === self::ACTION_PACK_ADD_BRANCH) {
            $pack = $this->_updatePack((int) $packId, $branches);
        } elseif ($action === self::ACTION_PACK_CHANGE_BRANCHES) {
            $pack = $this->_changePack((int) $packId, $branches);
        }

        return $this->_goPack($pack);
    }

    /**
     * Private parts of saving from different types of action: add, addBranches, removeBranches!
     */
    private function _createPack(array $branches): Pack
    {
        $name = $this->p('name', '');
        $name = preg_replace('/\W+/', '_', $name);

        $pack = new Pack();
        $pack->setName($name)
            ->setProject($this->project)
            ->setBranches($branches)
            ->save();

        return $pack;
    }

    /**
     * Add branches to pack
     */
    private function _updatePack(int $packId, array $branches = [])
    {
        $pack = Pack::getById($packId);
        $branches = array_unique(array_merge($pack->getBranches(), $branches));

        $pack
            ->setBranches($branches)
            ->save();

        return $pack;
    }

    /**
     * Remove branches from pack
     */
    private function _changePack(int $packId, array $branches = [])
    {
        $oldBranches = $this->p('oldBranches');

        $pack = Pack::getById($packId);
        $packBranches = $pack->getBranches();

        $oldBranches = json_decode($oldBranches, true);
        $branchesToRemove = array_diff($oldBranches, $branches);

        $newBranchesIdx   = array_flip($packBranches);
        foreach ($branchesToRemove as $branch) {
            unset($newBranchesIdx[$branch]);
        }
        $packBranches = array_flip($newBranchesIdx);

        $pack
            ->setBranches($packBranches)
            ->save();

        return $pack;
    }

    private function _goPack(Pack $pack)
    {
        if ($this->p('return')) {
            return $this->response->withRedirect(
                $this->request->getServerParam('HTTP_REFERER')
            );
        }

        return $this->response->withRedirect('/packs/' . $pack->getId());
    }
}
