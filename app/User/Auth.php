<?php

namespace User;

use Admin\App;
use Service\Auth\AuthInterface;
use Service\Data;
use Service\User;

class Auth implements AuthInterface
{
    const USER_ID = 'id';
    const USER_NAME = 'name';
    const USER_PASS = 'pass';
    const USER_LOGIN = 'login';
    const USER_ANONIM = 'Anonimus';
    const USER_ANONIM_TOKEN = 'cfcd208495d565ef66e7dff9f98764da';

    /** @var string */
    private $token = '';

    /** @var ?User */
    private $user;
    
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function loadUser(): void
    {
        $auth = Data::scope(App::DATA_SESSIONS)->getById($this->token);
        if ($auth !== null) {
            $this->user = User::getByLogin($auth[$this->token]);
        }
        if ($this->token === self::USER_ANONIM_TOKEN){
            $this->user = $this->getAnonim();
        }
    }

    private function getAnonim(): User
    {
        return (new User())
            ->setId(self::USER_ANONIM_TOKEN)
            ->setLogin(self::USER_ANONIM);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->user->getId();
    }

    public function getUserName(): string
    {
        return $this->user->getName()
            ?? $this->user->getLogin()
            ?? self::USER_ANONIM;
    }

    public function getUserLogin(): string
    {
        return $this->user->getLogin() ?? self::USER_ANONIM;
    }

    public function isAuthenticated(): bool
    {
        if ($this->user && $this->getUserId() !== self::USER_ANONIM_TOKEN) {
            return true;
        }

        return false;
    }

    public function isSshKeyExists(): bool
    {
        return file_exists(SSH_KEYS_DIR . '/' . $this->getUserLogin());
    }
}
