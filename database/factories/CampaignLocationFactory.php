<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Campaign;
use App\Models\CampaignLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignLocation>
 */
class CampaignLocationFactory extends Factory
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
            'location_id' => Location::factory(),
        ];
    }
}
