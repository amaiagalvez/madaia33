<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyAssignment>
 */
class PropertyAssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'owner_id' => Owner::factory(),
            'start_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'end_date' => null,
            'admin_validated' => false,
            'owner_validated' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state([
            'end_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ]);
    }

    public function validated(): static
    {
        return $this->state([
            'admin_validated' => true,
            'owner_validated' => true,
        ]);
    }
}
