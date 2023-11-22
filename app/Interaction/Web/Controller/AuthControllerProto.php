<?php

namespace Interaction\Web\Controller;

use Admin\App;
use Interaction\Base\Controller\ControllerProto;
use \Service\Data;

class AuthControllerProto extends ControllerProto
{
    public function before()
    {
        $data = Data::scope(App::DATA_USERS)->getAll();

        /** @var \User\Auth $auth */
        $auth = $this->app->getAuth();

        if(!$data && empty($data)){
            $auth->setToken(\User\Auth::USER_ANONIM_TOKEN);
        } else {
            $auth->setToken($this->app->getRequest()->getCookieParam('tkn'));
        }

        $auth->loadUser();
        $auth->setUser($auth->getUser());

        if (!$auth->isAuthenticated()) {
            $this->app->stopAndRedirectTo('/web/auth/login');
        }

        if (!$this->isEnabled()) {
            $this->app->stopAndRedirectTo('/web/errors/403');
        }

        parent::before();
    }

    public function isEnabled(): bool
    {
        return true;
    }

}
