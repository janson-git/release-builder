<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'name' => 'nullable|string',
            'password' => 'required|string',
            'confirm_password' => 'required|string|same:password',
        ];
    }

    public function getEmail(): string
    {
        return $this->validated('email');
    }

    public function getName(): string
    {
        return $this->validated('name');
    }

    public function getPassword(): string
    {
        return $this->get('password');
    }
}
