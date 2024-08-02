<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;

class ReleaseBranches implements \JsonSerializable, Arrayable
{
    protected array $commonBranches = [];
    protected array $serviceBranches = [];

    public function setCommonBranches(array $branches): void
    {
        $this->commonBranches = $branches;
    }

    public function addCommonBranches(array $branches, int $sandboxId): void
    {
        $merge = array_merge($this->commonBranches, $branches);
        $this->commonBranches = array_unique($merge);
    }

    public function setServiceBranches(array $branches, int $serviceId): void
    {
        $this->serviceBranches[$serviceId] = $branches;
    }

    public function addServiceBranches(array $branches, int $serviceId): void
    {
        if (! isset($this->serviceBranches[$serviceId])) {
            $this->serviceBranches[$serviceId] = $branches;
        } else {
            $merge = array_merge($this->serviceBranches[$serviceId], $branches);
            $this->serviceBranches[$serviceId] = array_unique($merge);
        }
    }

    public function getAllBranchesAsList(): array
    {
        $list = $this->commonBranches;

        foreach ($this->serviceBranches as $serviceId => $branches) {
            $list = array_merge($list, $branches);
        }

        return array_unique($list);
    }

    public function getCommonBranches(): array
    {
        return $this->commonBranches;
    }

    public function getServiceBranches(int $serviceId): array
    {
        return $this->serviceBranches[$serviceId] ?? [];
    }

    public function jsonSerialize(): mixed
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        $branches = [];
        $branches['common'] = $this->commonBranches;

        foreach ($this->serviceBranches as $serviceId => $list) {
            $branches[$serviceId] = $list;
        }

        return $branches;
    }
}
