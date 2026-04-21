<?php

use App\Models\User;
use App\Models\NoticeTag;
use App\Models\Construction;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns exactly assigned construction tags for manager', function () {
    $manager = User::factory()->create();
    $assignedCount = fake()->numberBetween(1, 4);

    $assignedConstructions = Construction::factory()->count($assignedCount)->create();
    Construction::factory()->count(2)->create();

    $manager->constructions()->sync($assignedConstructions->pluck('id')->all());

    $expectedSlugs = $assignedConstructions
        ->map(fn (Construction $construction): string => $construction->slug)
        ->sort()
        ->values()
        ->all();

    $selectedSlugs = NoticeTag::query()
        ->whereIn('slug', $manager->constructions()->pluck('constructions.slug')->all())
        ->pluck('slug')
        ->sort()
        ->values()
        ->all();

    expect($selectedSlugs)->toBe($expectedSlugs)
        ->and(Collection::make($selectedSlugs)->count())->toBe($assignedCount);
})->repeat(2);
