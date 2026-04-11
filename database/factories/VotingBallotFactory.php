<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VotingBallot>
 */
class VotingBallotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voting_id' => Voting::factory(),
            'owner_id' => Owner::factory(),
            'cast_by_user_id' => User::factory(),
            'cast_ip_address' => fake()->ipv4(),
            'is_in_person' => false,
            'voted_at' => now()->subHour(),
        ];
    }
}
