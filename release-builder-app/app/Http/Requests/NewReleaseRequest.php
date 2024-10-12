<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewReleaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'filter' => 'string|nullable',
            // services - int[]
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            // branches - string[]
            'branches' => 'required|array',
            'branches.*' => 'string',
        ];
    }

    public function getReleaseName(): string
    {
        return $this->validated('name');
    }

    /**
     * @return array|int[]
     */
    public function getServiceIds(): array
    {
        return $this->validated('service_ids');
    }

    /**
     * @return array|string[]
     */
    public function getBranches(): array
    {
        return $this->validated('branches');
    }

    public function getFilter(): ?string
    {
        return $this->validated('filter');
    }
}
