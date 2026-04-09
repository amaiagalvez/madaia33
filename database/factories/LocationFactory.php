<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['portal', 'garage', 'storage']),
            'code' => fake()->unique()->bothify('##-?'),
            'name' => fake()->bothify('Location ##'),
        ];
    }

    public function portal(): static
    {
        return $this->state(['type' => 'portal']);
    }

    public function garage(): static
    {
        return $this->state(['type' => 'garage']);
    }

    public function storage(): static
    {
        return $this->state(['type' => 'storage']);
    }
}
