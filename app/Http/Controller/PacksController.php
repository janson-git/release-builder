<?php

namespace App\Http\Controller;

use Admin\App;
use Commands\Command\Pack\CheckpointCreateCommand;
use Psr\Http\Message\ResponseInterface;
use Service\Pack;

class PacksController extends AbstractController
{
    public function show(int $id): ResponseInterface
    {
        $pack = Pack::getById($id);
        $pack->getNode()->loadBranches();

        $this->setTitle(__('pack') . ' ' , $pack->getName());
        if ($pack->getUser() !== null) {
            $this->setSubTitle('owned by @' . $pack->getUser()->getLogin());
        }
        $this->view->setAction('/projects/' . $pack->getProject()->getId(), 'Back to project');
        $node = $pack->getNode();
        $packReposByBranches = $node->getToMasterStatus($pack->getBranches());

        try {
            $pack->cloneMissedRepos();
            $pack->loadCheckpoints();


            if (!$pack->getCheckpoints()) {
                $pack->createCheckpoint();
                $pack->loadCheckpoints();
            }

        } catch (\Exception $e) {
            App::i()->log($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }

        $pack->loadCheckpoints();

        $dirs = array_intersect_key($node->getDirs(), $node->getRepos());

        return $this->view->render('packs/show.blade.php', [
            'pId'          => $pack->getProject()->getId(),
            'id'           => $pack->getId(),
            'branches'     => $packReposByBranches,
            'dirs'         => $dirs,
            'pack'         => $pack,
            'sandboxReady' => !$pack->getDirsToInit(),
        ]);
    }
}
