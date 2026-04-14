<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignRecipient>
 */
class CampaignRecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'owner_id' => Owner::factory(),
            'slot' => fake()->randomElement(['coprop1', 'coprop2']),
            'contact' => fake()->safeEmail(),
            'tracking_token' => bin2hex(random_bytes(32)),
            'status' => 'pending',
            'error_message' => null,
        ];
    }
}
