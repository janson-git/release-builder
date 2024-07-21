<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserSshKeyController extends Controller
{
    public function edit(): Response
    {
        return response()->view('users/add-ssh-key', [
            'header' => 'Add SSH Key',
            'subheader' => 'Generate SSH key pair, add pub-key into your git-storage (github, gitlab, etc..), private-key put here',
            'user' => app()->auth->getUser(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => [
                'required',
                'string',
                'starts_with:-----BEGIN OPENSSH PRIVATE KEY-----',
                'ends_with:-----END OPENSSH PRIVATE KEY-----',
            ],
        ]);

        $key = $validated['key'];
        $key = str_replace("\r\n", "\n", trim($key)) . "\n";

        /** @var User $user */
        $user = app()->auth->getUser();
        $user->saveSshKey($key);

        return response()->redirectTo('/user');
    }
}
