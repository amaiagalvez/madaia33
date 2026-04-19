<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\Construction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Construction>
 */
class ConstructionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => today()->subDays(5),
            'ends_at' => today()->addDays(10),
            'is_active' => true,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addDay(),
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
