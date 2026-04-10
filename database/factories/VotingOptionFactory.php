<?php

namespace Database\Factories;

use App\Models\Voting;
use App\Models\VotingOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VotingOption>
 */
class VotingOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voting_id' => Voting::factory(),
            'label_eu' => fake()->sentence(2),
            'label_es' => fake()->sentence(2),
            'position' => 1,
        ];
    }
}
