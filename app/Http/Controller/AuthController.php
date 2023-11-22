<?php

namespace App\Http\Controller;

use Admin\App;
use Service\Data;
use Service\User;
use Slim\Http\Response;

class AuthController extends AbstractController
{
    public function login(): Response
    {
        $this->setTitle(__('login'));

        if ($this->request->isPost()) {

            $login = $this->p('login');
            $pass = $this->p('password');

            $user = User::getByLoginAndPass($login, $pass);
            if ($user !== null) {
                $token = $this->createToken($login);

                Data::scope(App::DATA_SESSIONS)
                    ->insertOrUpdate($token, $login)
                    ->write();

                return $this->app->getCookiesPipe()
                    ->addCookie($this->response, 'tkn', $token)
                    ->withRedirect('/projects');
            } else {
                return $this->response->withRedirect('/auth/login');
            }
        }

        return $this->view->render('auth/loginForm.blade.php');
    }

    public function logout(): Response
    {
        $token = $this->request->getCookieParam('tkn');

        Data::scope(App::DATA_SESSIONS)
            ->delete($token)
            ->write();

        // delete token cookie and go to login page
        return $this->app->getCookiesPipe()
            ->deleteCookie($this->response, 'tkn')
            ->withRedirect('/auth/login');
    }

    public function register(): Response
    {
        $this->setTitle(__('registration'));

        if ($this->request->isPost()) {
            $login = $this->p('login');
            $userName = $this->p('name', '');
            $userPassword1 = md5($this->p('password1'));
            $userPassword2 = md5($this->p('password2'));

            $user = User::getByLogin($login);

            //Создание пользователя
            if ($user === null && $userPassword1 === $userPassword2) {
                $user = new User();
                $user->setName($userName);
                $user->setLogin($login);
                $user->setPassword($userPassword1);
                $user->setId($this->createToken($login));

                $user->save();
            }

            // login new user and update session
            $sessionToken = $this->createToken($login);

            Data::scope(App::DATA_SESSIONS)
                ->insertOrUpdate($sessionToken, $login)
                ->write();

            return $this->app->getCookiesPipe()
                ->addCookie($this->response, 'tkn', $sessionToken)
                ->withRedirect('/projects');
        }

        return $this->view->render('auth/registerForm.blade.php');
    }

    protected function createToken(string $name): string
    {
        return md5(microtime() . $name);
    }
}
