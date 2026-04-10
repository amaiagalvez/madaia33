<?php

namespace Database\Factories;

use App\Models\Voting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voting>
 */
class VotingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_eu' => fake()->sentence(3),
            'name_es' => fake()->sentence(3),
            'question_eu' => fake()->sentence(),
            'question_es' => fake()->sentence(),
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addWeek(),
            'is_published' => true,
            'is_anonymous' => false,
        ];
    }

    public function current(): static
    {
        return $this->state([
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addDay(),
        ]);
    }

    public function future(): static
    {
        return $this->state([
            'starts_at' => today()->addDay(),
            'ends_at' => today()->addWeek(),
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function anonymous(): static
    {
        return $this->state(['is_anonymous' => true]);
    }
}
