<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'value' => fake()->sentence(),
            'section' => fake()->randomElement(Setting::allowedSections()),
        ];
    }

    public function forKey(string $key, ?string $value = null): static
    {
        return $this->state([
            'key' => $key,
            'value' => $value ?? fake()->sentence(),
        ]);
    }
}
