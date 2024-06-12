<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(): Response
    {
        return response()->view('auth/login', [
            'header' => 'Log In',
        ]);
    }

    public function store(Request $request)
    {
        $email = $request->get('email');
        $pass = $request->get('password');

        // TODO: add validation

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return \response()->redirectToRoute('login');
    }
//
//    public function logout(): Response
//    {
//        $token = $this->request->getCookieParam($this->getAuthCookieName());
//
//        Data::scope(App::DATA_SESSIONS)
//            ->delete($token)
//            ->write();
//
//        // delete token cookie and go to login page
//        return $this->app->getCookiesPipe()
//            ->deleteCookie($this->response, $this->getAuthCookieName())
//            ->withRedirect('/auth/login');
//    }
//
//    public function register(): Response
//    {
//        $this->setTitle(__('registration'));
//
//        if ($this->request->isPost()) {
//            $login = $this->p('login');
//            $userName = $this->p('name', '');
//            $userPassword1 = md5($this->p('password1'));
//            $userPassword2 = md5($this->p('password2'));
//
//            $user = User::getByLogin($login);
//
//            //Создание пользователя
//            if ($user === null && $userPassword1 === $userPassword2) {
//                $user = new User();
//                $user->setName($userName);
//                $user->setLogin($login);
//                $user->setPassword($userPassword1);
//                $user->setId($this->createToken($login));
//
//                $user->save();
//            }
//
//            // login new user and update session
//            $sessionToken = $this->createToken($login);
//
//            Data::scope(App::DATA_SESSIONS)
//                ->insertOrUpdate($sessionToken, $login)
//                ->write();
//
//            return $this->app->getCookiesPipe()
//                ->addCookie($this->response, $this->getAuthCookieName(), $sessionToken)
//                ->withRedirect('/projects');
//        }
//
//        return $this->view->render('auth/registerForm.blade.php');
//    }
//
//    private function createToken(string $name): string
//    {
//        return md5(microtime() . $name);
//    }
//
//    private function getAuthCookieName(): string
//    {
//        return 'tkn' . App::i()->getIdentify();
//    }
}
