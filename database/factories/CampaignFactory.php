<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by_user_id' => User::factory(),
            'subject_eu' => fake()->sentence(4),
            'subject_es' => fake()->sentence(4),
            'body_eu' => fake()->paragraph(),
            'body_es' => fake()->paragraph(),
            'channel' => fake()->randomElement(['email', 'sms', 'whatsapp', 'telegram']),
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
        ];
    }
}
