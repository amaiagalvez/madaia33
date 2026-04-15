<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignDocument>
 */
class CampaignDocumentFactory extends Factory
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
            'filename' => fake()->slug() . '.pdf',
            'path' => 'campaign-documents/' . fake()->uuid() . '.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1024, 1024 * 1024),
            'is_public' => fake()->boolean(),
        ];
    }
}
