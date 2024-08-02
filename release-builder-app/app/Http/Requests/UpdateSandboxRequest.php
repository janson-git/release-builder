<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSandboxRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // branches - string[]
            'branches' => 'required|array',
            'branches.*' => 'string',
        ];
    }

    /**
     * @return array|string[]
     */
    public function getBranches(): array
    {
        return $this->validated('branches');
    }
}
