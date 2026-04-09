<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titleEu = fake()->sentence(4);

        return [
            'slug' => Str::slug($titleEu).'-'.fake()->unique()->randomNumber(5),
            'title_eu' => $titleEu,
            'title_es' => fake()->sentence(4),
            'content_eu' => fake()->paragraphs(2, true),
            'content_es' => fake()->paragraphs(2, true),
            'is_public' => true,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Mark the notice as public.
     */
    public function public(): static
    {
        return $this->state(['is_public' => true]);
    }

    /**
     * Mark the notice as private (not public).
     */
    public function private(): static
    {
        return $this->state(['is_public' => false]);
    }

    /**
     * Notice with only Basque translation.
     */
    public function euOnly(): static
    {
        return $this->state([
            'title_es' => null,
            'content_es' => null,
        ]);
    }

    /**
     * Notice with only Spanish translation.
     */
    public function esOnly(): static
    {
        return $this->state([
            'title_eu' => null,
            'content_eu' => null,
        ]);
    }
}
