<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Construction;
use App\Models\ConstructionInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConstructionInquiry>
 */
class ConstructionInquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'construction_id' => Construction::factory(),
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'reply' => null,
            'replied_at' => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }
}
