<?php

namespace Database\Factories;

use App\Models\MessageReply;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageReply>
 */
class MessageReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_message_id' => ContactMessage::factory(),
            'reply_body' => fake()->paragraph(),
            'sent_at' => now(),
        ];
    }
}
