<?php

namespace Database\Factories;

use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignTrackingEvent>
 */
class CampaignTrackingEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_recipient_id' => CampaignRecipient::factory(),
            'campaign_document_id' => CampaignDocument::factory(),
            'event_type' => fake()->randomElement(['open', 'click', 'download', 'error']),
            'url' => fake()->optional()->url(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
