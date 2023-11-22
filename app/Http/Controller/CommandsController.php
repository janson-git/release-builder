<?php

namespace App\Http\Controller;

use Commands\CommandConfig;
use Commands\CommandContext;
use Commands\CommandRunner;
use Service\Slot\SlotStack;

class CommandsController extends AbstractController
{
    private $title    = '';
    private $subTitle = [];
    
    /**
     * @var CommandContext
     */
    private $context;
    
    public function apply()
    {
        $command       = $this->p('command');
        $contextString = $this->p('context');
        $userData      = $this->p('userData');

        $this->context = new CommandContext();
        $this->context->deserialize($contextString);
        $this->context->set(CommandContext::USER_CONTEXT, $userData);
        
//        if (!$this->context->getSlot() && $this->context->getPack()) {
//            $slots = $this->context->getPack()->getProject()->getSlotsPool()->loadProjectSlots()->getSlots();
//            $this->context->setSlot((new SlotStack())->setStack($slots)); 
//        }

        $this->_buildTitle();
        
        $runner = new CommandRunner();
        $runner->setContext($this->context);
        $runner->setCommandIdsToRun([$command]);

        $runner->run();

        return $this->view->render('command/apply.blade.php', [
            'context' => $runner->getContext(),
            'runner'  => $runner,
            'runtime' => $runner->getRuntime(),
            'packId'  => $this->context->getPack() ? $this->context->getPack()->getId() : '',
            'isPackDeleted' => ($command === CommandConfig::PACK_CLEAR_DATA),
        ]);
    }
    
    private function _buildTitle()
    {
        if ($this->context->getPack()) {
            $this->_addTitle(__('pack')  . ": {$this->context->getPack()->getName()}");
        }
        
        if ($this->context->getProject()) {
            $this->_addTitle(__('project') . ": {$this->context->getProject()->getName()}");
        }
        
        if ($this->context->getCheckpoint()) {
            $this->_addTitle(__('build') . ": {$this->context->getCheckpoint()->getName()}");
        }

        if ($this->context->getSlot()) {
            $this->_addTitle(__('server') . ": {$this->context->getSlot()->getDescription()}");
        }
    }
    
    private function _addTitle($text): void
    {
        if (!$this->title) {
            $this->title = $text;
            $this->setTitle($text);
            
            return;
        }
        
        $this->subTitle[] = $text;
        $this->setSubTitle(implode('<br>', $this->subTitle));
    }
}
