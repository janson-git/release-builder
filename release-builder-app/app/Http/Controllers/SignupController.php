<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SignupController extends Controller
{
    public function show(): Response
    {
        return response()->view('auth/signup', [
            'header' => 'Sign Up',
        ]);
    }

////    public function logout(): Response
////    {
////        $token = $this->request->getCookieParam($this->getAuthCookieName());
////
////        Data::scope(App::DATA_SESSIONS)
////            ->delete($token)
////            ->write();
////
////        // delete token cookie and go to login page
////        return $this->app->getCookiesPipe()
////            ->deleteCookie($this->response, $this->getAuthCookieName())
////            ->withRedirect('/auth/login');
////    }
////
    public function store(Request $request): Response
    {
        $email = $request->get('email');
        $userName = $request->get('name', '');
        $userPassword1 = md5($request->get('password'));
        $userPassword2 = md5($request->get('confirm_password'));

        // TODO: add validation

        $user = User::where(['email' => $email])->first();

        //Создание пользователя
        if ($user === null && $userPassword1 === $userPassword2) {
            $user = new User();
            $user->setName($userName);
            $user->setLogin($email);
            $user->setPassword($userPassword1);
            $user->setId($this->createToken($email));

            $user->save();
        }

        // login new user and update session
        $sessionToken = $this->createToken($email);

        Data::scope(App::DATA_SESSIONS)
            ->insertOrUpdate($sessionToken, $email)
            ->write();

        return $this->app->getCookiesPipe()
            ->addCookie($this->response, $this->getAuthCookieName(), $sessionToken)
            ->withRedirect('/projects');
    }
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
