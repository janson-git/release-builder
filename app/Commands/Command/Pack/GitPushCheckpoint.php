<?php

namespace Commands\Command\Pack;

use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;

class GitPushCheckpoint extends CommandProto
{
    public function run()
    {
        $checkpoint = $this->context->getCheckpoint()->getName();
        
        $sshPrivateKey = SSH_KEYS_DIR . '/' . App::i()->getAuth()->getUserLogin();
    
        if (!file_exists($sshPrivateKey)) {
            $this->runtime->log('specific ssh private key "'.$sshPrivateKey.'" not found. Used default.', 'git config');
            $sshPrivateKey = null;
        }
        
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->setSshKeyPath($sshPrivateKey);
            $repo->fetch();
            $repo->checkout($checkpoint);
            $repo->push([$checkpoint]);
            $repo->setSshKeyPath(null);
    
            $this->runtime[$repo->getPath()] = $repo->getLastOutput();
        }

        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_PUSH_TO_ORIGIN;
    }
    
    public function getHumanName()
    {
        return __('push_to_git');
    }
    
    public function isConfirmRequired(): bool
    {
        return true;
    }
}
