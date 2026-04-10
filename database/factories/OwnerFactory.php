<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'coprop1_name' => fake()->name(),
            'coprop1_dni' => strtoupper(fake()->bothify('########?')),
            'coprop1_phone' => fake()->phoneNumber(),
            'coprop1_email' => fake()->unique()->safeEmail(),
            'coprop2_name' => null,
            'coprop2_dni' => null,
            'coprop2_phone' => null,
            'coprop2_email' => null,
        ];
    }

    public function withSecondCoProp(): static
    {
        return $this->state([
            'coprop2_name' => fake()->name(),
            'coprop2_dni' => strtoupper(fake()->bothify('########?')),
            'coprop2_phone' => fake()->phoneNumber(),
            'coprop2_email' => fake()->safeEmail(),
        ]);
    }
}
