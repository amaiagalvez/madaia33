<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Owner;
use App\SupportedLocales;
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
            'coprop1_surname' => fake()->lastName(),
            'coprop1_dni' => strtoupper(fake()->bothify('########?')),
            'coprop1_phone' => fake()->phoneNumber(),
            'coprop1_email' => fake()->unique()->safeEmail(),
            'language' => SupportedLocales::default(),
            'coprop2_name' => null,
            'coprop2_surname' => null,
            'coprop2_dni' => null,
            'coprop2_phone' => null,
            'coprop2_email' => null,
        ];
    }

    public function withSecondCoProp(): static
    {
        return $this->state([
            'coprop2_name' => fake()->name(),
            'coprop2_surname' => fake()->lastName(),
            'coprop2_dni' => strtoupper(fake()->bothify('########?')),
            'coprop2_phone' => fake()->phoneNumber(),
            'coprop2_email' => fake()->safeEmail(),
        ]);
    }
}
