<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $type
 * @property int $name
 * @property int $value
 */
class Setting extends Model
{
    public $timestamps = false;

    public $fillable = [
        'type',
        'name',
        'value',
    ];

    /**
     * Wrapper to access GitRepository bound to current service
     * @return Attribute
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value) {
                return match ($this->type) {
                    'boolean' => (bool) $value,
                    'integer' => (integer) $value,
                    'float' => (float) $value,
                    default => (string) $value,
                };
            },
            set: function (mixed $value) {
                return match ($this->type) {
                    'boolean' => (bool) $value,
                    'integer' => (integer) $value,
                    'float' => (float) $value,
                    default => (string) $value,
                };
            }
        );
    }

    public static function getByName(string $name): Setting
    {
        return Setting::where('name', $name)->first();
    }

    public static function getValueByName(string $name): mixed
    {
        return Setting::where('name', $name)->first()?->value;
    }
}
