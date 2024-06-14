<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'repository_path' => 'required|string',
        ];
    }

    public function getRepositoryPath(): string
    {
        return $this->validated('repository_path');
    }
}
