<?php

namespace Database\Factories;

use App\Models\Voting;
use App\Models\VotingOption;
use App\Models\VotingOptionTotal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VotingOptionTotal>
 */
class VotingOptionTotalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voting_id' => Voting::factory(),
            'voting_option_id' => VotingOption::factory(),
            'votes_count' => fake()->numberBetween(0, 500),
            'pct_total' => fake()->randomFloat(4, 0, 100),
        ];
    }
}
