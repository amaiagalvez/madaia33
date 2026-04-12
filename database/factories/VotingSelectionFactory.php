<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VotingSelection>
 */
class VotingSelectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voting_id' => Voting::factory(),
            'voting_ballot_id' => VotingBallot::factory(),
            'owner_id' => Owner::factory(),
            'voting_option_id' => VotingOption::factory(),
        ];
    }
}
