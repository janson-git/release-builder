<?php

namespace Service;

use Admin\App;

class User
{
    // map to Data scope fields
    protected const F_ID = 'id';
    protected const F_NAME = 'name';
    protected const F_LOGIN = 'login';
    protected const F_PASS = 'pass';
    protected const F_COMMITTER_NAME = 'committerName';
    protected const F_COMMITTER_EMAIL = 'committerEmail';
    protected const F_ACCESS_TOKEN = 'githubToken';
    protected const F_ACCESS_TOKEN_EXPIRATION_DATE = 'githubTokenExpirationDate';

    /** @var string This is login  */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $password;

    /** @var string */
    private $login;

    /** @var string */
    private $email;

    /** @var string */
    private $commitAuthorName;

    /** @var string */
    private $commitAuthorEmail;

    private ?string $accessToken = null;
    private ?string $accessTokenExpirationDate = null;

    public function __construct() {}

    public static function getByLogin(string $login): ?self
    {
        $user = new self();

        return $user->loadBy($login);
    }

    public static function getByLoginAndPass(string $login, string $password): ?self
    {
        $user = new self();

        return $user->loadBy($login, $password);
    }

    private function loadBy(string $login, ?string $password = null): ?self
    {
        /* load data */
        $data = Data::scope(App::DATA_USERS)->getAll();

        if (!isset($data[$login])) {
            return null;
        }

        $userData = $data[$login];

        if ($password !== null && $userData[self::F_PASS] !== md5($password)) {
            return null;
        }

        $this->login = $login;
        $this->id = $userData[self::F_ID];
        $this->name = $userData[self::F_NAME];
        $this->password = $userData[self::F_PASS];
        $this->commitAuthorName = $userData[self::F_COMMITTER_NAME] ?? '';
        $this->commitAuthorEmail = $userData[self::F_COMMITTER_EMAIL] ?? '';
        $this->accessToken = $userData[self::F_ACCESS_TOKEN] ?? '';
        $this->accessTokenExpirationDate = $userData[self::F_ACCESS_TOKEN_EXPIRATION_DATE] ?? '';

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getCommitAuthorName(): ?string
    {
        return $this->commitAuthorName;
    }

    public function setCommitAuthorName(string $name): self
    {
        $this->commitAuthorName = $name;
        return $this;
    }

    public function getCommitAuthorEmail(): ?string
    {
        return $this->commitAuthorEmail;
    }

    public function setCommitAuthorEmail(string $email): self
    {
        $this->commitAuthorEmail = $email;
        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;
        return $this;
    }

    public function getAccessTokenExpirationDate(): ?string
    {
        return $this->accessTokenExpirationDate;
    }

    public function setAccessTokenExpirationDate(string $date): self
    {
        $this->accessTokenExpirationDate = $date;
        return $this;
    }

    public function save(): void
    {
        if ($this->login === null) {
            throw new \Exception('User login not defined!');
        }

        $userData = [
            self::F_NAME => $this->name,
            self::F_PASS => $this->password,
            self::F_ID => $this->id,
            self::F_LOGIN => $this->login,
            self::F_COMMITTER_NAME => $this->commitAuthorName,
            self::F_COMMITTER_EMAIL => $this->commitAuthorEmail,
            self::F_ACCESS_TOKEN => $this->accessToken,
            self::F_ACCESS_TOKEN_EXPIRATION_DATE => $this->accessTokenExpirationDate,
        ];

        Data::scope(App::DATA_USERS)
            ->insertOrUpdate($this->login, $userData)
            ->write();
    }
}
