<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => fake()->uuid().'.jpg',
            'path' => 'images/'.fake()->uuid().'.jpg',
            'alt_text_eu' => fake()->sentence(4),
            'alt_text_es' => fake()->sentence(4),
        ];
    }

    /**
     * Image with only Basque alt text.
     */
    public function euOnly(): static
    {
        return $this->state([
            'alt_text_es' => null,
        ]);
    }

    /**
     * Image with only Spanish alt text.
     */
    public function esOnly(): static
    {
        return $this->state([
            'alt_text_eu' => null,
        ]);
    }
}
