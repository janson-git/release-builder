<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class SignupController extends Controller
{
    public function show(): Response
    {
        return response()->view('auth/signup', [
            'header' => 'Sign Up',
        ]);
    }

    public function store(SignupRequest $request): RedirectResponse
    {
        $email = $request->getEmail();
        $userName = $request->getName();
        $userPassword = bcrypt($request->getPassword());

        $user = User::where(['email' => $email, 'password' => $userPassword])
            ->first();

        if ($user === null) {
            $user = new User();
            $user->name = $userName;
            $user->email = $email;
            $user->password = $userPassword;

            $user->save();

            return redirect()->intended('/login');
        }

        return back()->withErrors([
            'email' => 'This email already used'
        ]);
    }
}
