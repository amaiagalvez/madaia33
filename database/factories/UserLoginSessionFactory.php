<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\UserLoginSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserLoginSession>
 */
class UserLoginSessionFactory extends Factory
{
    /**
     * @var class-string<UserLoginSession>
     */
    protected $model = UserLoginSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'impersonator_user_id' => null,
            'session_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'logged_in_at' => now()->subMinutes(fake()->numberBetween(5, 240)),
            'logged_out_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(function (array $attributes): array {
            $loggedInAt = isset($attributes['logged_in_at'])
                ? Carbon::parse((string) $attributes['logged_in_at'])
                : now()->subHour();

            return [
                'logged_out_at' => $loggedInAt->copy()->addMinutes(45),
            ];
        });
    }
}
