<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->bothify('?#');

        return [
            'location_id' => Location::factory(),
            'code' => fn (array $attributes): string => (string) ($attributes['name'] ?? $name),
            'name' => $name,
            'community_pct' => fake()->randomFloat(4, 0.1, 5.0),
            'location_pct' => fake()->randomFloat(4, 0.1, 10.0),
        ];
    }

    public function forStorage(): static
    {
        return $this->state([
            'community_pct' => fake()->randomFloat(4, 0.1, 5.0),
            'location_pct' => fake()->randomFloat(4, 0.1, 10.0),
        ]);
    }
}
