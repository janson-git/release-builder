<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->colorName . ' ' . fake()->name . ' ' . fake()->monthName;
        return [
            'name' => $name,
            'repository_url' => 'https://fake-github.domain' . '/' . fake()->slug(3),
        ];
    }
}
