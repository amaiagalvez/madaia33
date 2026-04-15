<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\CampaignTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignTemplate>
 */
class CampaignTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'subject_eu' => fake()->sentence(4),
            'subject_es' => fake()->sentence(4),
            'body_eu' => fake()->paragraph(),
            'body_es' => fake()->paragraph(),
            'channel' => fake()->randomElement(['email', 'sms', 'whatsapp', 'telegram']),
            'created_by_user_id' => User::factory(),
        ];
    }
}
