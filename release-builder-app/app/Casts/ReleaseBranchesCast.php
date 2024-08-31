<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\ReleaseBranches;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ReleaseBranchesCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ReleaseBranches
    {
        $obj = new ReleaseBranches();

        $array = $value ? json_decode($value, true) : [];

        // if we don't have 'common' key in array - just use this as common
        // otherwise fill ReleaseBranches with Common and Service branches
        if (! array_key_exists('common', $array)) {
            $obj->setCommonBranches($array);

            return $obj;
        }

        foreach ($array as $key => $branches) {
            if ($key === 'common') {
                $obj->setCommonBranches($branches);
            } else {
                $obj->setServiceBranches($branches, $key);
            }
        }

        return $obj;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (! $value instanceof ReleaseBranches) {
            throw new InvalidArgumentException('The given value is not an ReleaseBranches instance.');
        }

        return json_encode($value->toArray());
    }
}
