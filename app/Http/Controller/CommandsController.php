<?php

namespace App\Http\Controller;

use Commands\CommandConfig;
use Commands\CommandContext;
use Commands\CommandRunner;

class CommandsController extends AbstractController
{
    private string $title    = '';
    private array $subTitle = [];
    
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

        ($command !== CommandConfig::PACK_CLEAR_DATA && $this->context->getPack())
            ? $this->view->setAction("/packs/{$this->context->getPack()->getId()}", __('back_to_pack'))
            : $this->view->setAction("/projects/{$this->context->getProject()->getId()}", __('back_to_project'));

        $this->_buildTitle();
        
        $runner = new CommandRunner();
        $runner->setContext($this->context);
        $runner->setCommandIdsToRun([$command]);

        $runner->run();

        return $this->view->render('command/apply.blade.php', [
            'context' => $runner->getContext(),
            'runner'  => $runner,
            'runtime' => $runner->getRuntime(),
            'pack'    => $this->context->getPack() ?? null,
            'commandName' => CommandConfig::getCommand($command)->getHumanName(),
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
