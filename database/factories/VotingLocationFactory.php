<?php

namespace Database\Factories;

use App\Models\Voting;
use App\Models\Location;
use App\Models\VotingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VotingLocation>
 */
class VotingLocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voting_id' => Voting::factory(),
            'location_id' => Location::factory(),
        ];
    }
}
