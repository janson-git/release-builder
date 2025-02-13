<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    public function show(): Response
    {
        /** @var User $user */
        $user = app()->auth->getUser();

        return response()->view('users/show', [
            'header' => 'Profile',
            'user' => $user,
        ]);
    }
}
