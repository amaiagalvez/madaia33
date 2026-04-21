<?php

use App\Models\NoticeTag;
use App\Models\Construction;

it('creates a construction notice tag when a construction is created', function (): void {
    $construction = Construction::factory()->create([
        'title' => 'Rehabilitacion Fachada',
        'slug' => 'rehabilitacion-fachada',
    ]);

    $tag = NoticeTag::query()->where('slug', $construction->slug)->first();

    expect($tag)->not->toBeNull()
        ->and($tag?->name_eu)->toBe('Rehabilitacion Fachada')
        ->and($tag?->name_es)->toBe('Rehabilitacion Fachada');
});

it('updates construction notice tag slug and name when construction title changes', function (): void {
    $construction = Construction::factory()->create([
        'title' => 'Fatxada zaharra',
        'slug' => 'fatxada-zaharra',
    ]);

    $construction->update([
        'title' => 'Fatxada berria',
        'slug' => 'fatxada-berria',
    ]);

    expect(NoticeTag::query()->where('slug', 'fatxada-zaharra')->exists())->toBeFalse();

    $updatedTag = NoticeTag::query()->where('slug', 'fatxada-berria')->first();

    expect($updatedTag)->not->toBeNull()
        ->and($updatedTag?->name_eu)->toBe('Fatxada berria')
        ->and($updatedTag?->name_es)->toBe('Fatxada berria');
});
