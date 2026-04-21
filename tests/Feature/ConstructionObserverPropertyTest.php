<?php

use App\Models\NoticeTag;
use App\Models\Construction;

it('creates exactly one observer tag per construction', function () {
    $title = fake()->sentence(3);
    $construction = Construction::factory()->create([
        'title' => $title,
    ]);

    $slug = $construction->slug;

    expect(NoticeTag::query()->where('slug', $slug)->count())->toBe(1)
        ->and(NoticeTag::query()->where('slug', $slug)->value('name_eu'))->toContain($construction->title);
})->repeat(2);
