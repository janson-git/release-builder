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
            // services - int[]
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            // branches - services able to have different branch names for same
            //   tasks, we need to get array of services with branches:
            // [
            //    <SERVICE_ID_1> => [
            //        'task-xx-branch-name',
            //        'task-yy-branch-name',
            //    ],
            //    <SERVICE_ID_2> => [
            //        'task-xx-branch-name',
            //        'task-aa-branch-name',
            //        'task-yy-branch-name-in-other-service',
            //    ],
            // ]
            'branches' => 'required|array',
//            'branches.*' => 'array',
        ];
    }

    public function getReleaseName(): string
    {
        return $this->validated('name');
    }

    public function getServiceIds(): string
    {
        return $this->validated('service_ids');
    }

    public function getBranchesGroupedBySericeIds(): string
    {
        return $this->validated('branches');
    }
}
