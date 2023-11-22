<?php

namespace Commands\Command;

use Commands\Command\Build\BuildReleaseByDirectories;
use Commands\Command\Delivery\SourcesBuildArchiveCreate;
use Commands\Command\Pack\CheckpointMergeBranches;
use Commands\CommandConfig;

class MakeBuildForDownload extends DeployCommandProto
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
            $command->prepare();
            $command->run();
        }
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_FOR_DOWNLOAD;
    }
    
    public function getHumanName()
    {
        return __('build_to_download');
    }
    
    /**
     * @return CommandProto[]
     */
    public function getSubcommands()
    {
        return [
            new CheckpointMergeBranches(),
            new BuildReleaseByDirectories(),
            new SourcesBuildArchiveCreate(),
        ];
    }
    
    public function isPrimary(): bool
    {
        return true;
    }
}
