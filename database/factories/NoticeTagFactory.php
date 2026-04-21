<?php

namespace Database\Factories;

use App\Models\NoticeTag;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NoticeTag>
 */
class NoticeTagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'name_eu' => Str::title($name),
            'name_es' => fake()->optional()->sentence(2),
        ];
    }
}
