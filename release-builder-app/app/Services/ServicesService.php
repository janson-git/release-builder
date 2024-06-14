<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Collection;

class ServicesService
{
    public function getServices(): Collection
    {
        return Service::all();
    }

    public function getService(int $id): Service
    {
        return Service::find($id);
    }
}
