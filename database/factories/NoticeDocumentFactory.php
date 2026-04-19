<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Support\Str;
use App\Models\NoticeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NoticeDocument>
 */
class NoticeDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->word() . '.pdf';

        return [
            'notice_id' => Notice::factory(),
            'token' => (string) Str::uuid(),
            'filename' => $filename,
            'path' => 'notices/' . fake()->uuid() . '/' . $filename,
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1024, 5000000),
            'is_public' => fake()->boolean(),
        ];
    }
}
