<?php

use App\Models\Construction;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only constructions active by date and status', function () {
    $active = Construction::factory()->create([
        'starts_at' => today()->subDays(fake()->numberBetween(1, 5)),
        'ends_at' => today()->addDays(fake()->numberBetween(1, 5)),
        'is_active' => true,
    ]);

    Construction::factory()->create([
        'starts_at' => today()->addDays(2),
        'ends_at' => today()->addDays(10),
        'is_active' => true,
    ]);
    Construction::factory()->create([
        'starts_at' => today()->subDays(10),
        'ends_at' => today()->subDay(),
        'is_active' => true,
    ]);
    Construction::factory()->create([
        'starts_at' => today()->subDays(3),
        'ends_at' => today()->addDays(3),
        'is_active' => false,
    ]);

    $activeIds = Construction::query()->active()->pluck('id')->all();

    expect(Collection::make($activeIds))->toContain($active->id)
        ->and(Collection::make($activeIds)->count())->toBe(1);
})->repeat(2);
