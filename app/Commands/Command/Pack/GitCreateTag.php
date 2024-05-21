<?php

namespace Commands\Command\Pack;

use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Commands\CommandContext;
use Commands\PackOwnerAuthorityTrait;

class GitCreateTag extends CommandProto
{
    use PackOwnerAuthorityTrait;

    public const QUESTION_TAG = 'tag';

    public function getId(): string
    {
        return CommandConfig::CHECKPOINT_CREATE_TAG;
    }

    public function getHumanName(): string
    {
        return __('create_git_tag');
    }

    public function run()
    {
        $checkpoint = $this->context->getCheckpoint()->getName();
        $tag = $this->getContext()->get(CommandContext::USER_CONTEXT)[self::QUESTION_TAG] ?? null;
        if (!$tag) {
            $this->runtime->log(sprintf('tag `%s` is invalid', $tag));
            return;
        }

        $sshPrivateKey = SSH_KEYS_DIR . '/' . App::i()->getAuth()->getUserLogin();
        if (!file_exists($sshPrivateKey)) {
            $this->runtime->log('specific ssh private key "'.$sshPrivateKey.'" not found. Used default.', 'git config');
            $sshPrivateKey = null;
        }

        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->setSshKeyPath($sshPrivateKey);

            $repo->fetch();
            $repo->checkout($checkpoint);
            $repo->createTag($tag);
            $repo->pushTags();

            $repo->setSshKeyPath(null);

            $this->runtime[$repo->getPath()] = $repo->getLastOutput();
        }
    }

    /**
     * @return array
     * @throws \Git\GitException
     */
    public function isQuestion() : array
    {
        $lastTag = $this->getLastTag();
        return $this->createQuestion(
            self::QUESTION_TAG,
            'Set up new tag. Last tag is ' . trim($lastTag),
            !empty($lastTag) ? $this->getNextVersion($lastTag) : '1.0.0'
        );
    }

    /**
     * Выглядит, как отдельная фабрика. Перенеси при переиспользовании в отдельный объект
     * @param string $field
     * @param string $question
     * @param string $placeholder
     * @return array
     */
    private function createQuestion(string $field, string $question, string $placeholder) : array
    {
        return [
            'field'       => $field,
            'question'    => $question,
            'placeholder' => $placeholder,
        ];
    }

    private function getNextVersion(?string $lastVersion)
    {
        $dotParts = explode('.', $lastVersion);
        $lastItem = array_pop($dotParts);
        if (is_numeric($lastItem)) {
            $lastItem++;
            $dotParts[] = $lastItem;
        }

        return implode('.', $dotParts);
    }

    private function getLastTag() : ?string
    {
        $sshPrivateKey = SSH_KEYS_DIR . '/' . App::i()->getAuth()->getUserLogin();
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            try {
                $repo->setSshKeyPath($sshPrivateKey);
                return $repo->getLastTag();
            } finally {
                $repo->setSshKeyPath(null);
                $this->runtime[$repo->getPath()] = $repo->getLastOutput();
            }
        }

        return null;
    }
}
