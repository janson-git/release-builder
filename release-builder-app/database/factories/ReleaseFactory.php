<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Release>
 */
class ReleaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $branches = [];
        for ($i = 1; $i < rand(2, 5); $i++) {
            $branches[] = 'task_' . rand(9000, 9999);
        }

        return [
            'name' => fake()->name(),
            'branches' => $branches,
            'delivery_date' => now()->addDays(rand(3, 10)),
        ];
    }
}
