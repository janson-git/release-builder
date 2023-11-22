<?php

namespace Commands\Command;

use Commands\Command\Build\BuildReleaseByDirectories;
use Commands\Command\Install\DeployBuildLocal;
use Commands\Command\Install\RunSetupScripts;
use Commands\Command\Install\SupervisorRestart;
use Commands\Command\Pack\CheckpointMergeBranches;
use Commands\CommandConfig;

class LocalDeploy extends DeployCommandProto
{
    /**
     * @var CommandProto[]
     */
    protected $commands = [];

    public function run()
    {
        foreach ($this->getSubcommands() as $command) {
            $this->runtime->startSection($command->getId(), $command->getHumanName());
            $command->setContext($this->context);
            
            $command->setRuntime($this->runtime);
//            $command->setSandbox($this->sandbox);
//            $command->setCheckpoint($this->checkpoint);
            $command->prepare();
            $command->run();
        }
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_AND_DEPLOY;
    }
    
    public function getHumanName()
    {
        return __('build_and_deploy_locally');
    }
    
    /**
     * @return CommandProto[]
     */
    public function getSubcommands()
    {
        return [
            new CheckpointMergeBranches(),
            new BuildReleaseByDirectories(),
            (new DeployBuildLocal())->setDeployRoot($this->deployRoot),
            (new RunSetupScripts())->setDeployRoot($this->deployRoot),
            new SupervisorRestart()
        ];
    }
    
    public function isPrimary(): bool
    {
        return true;
    }
}
