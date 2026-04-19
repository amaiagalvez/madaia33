<?php

use App\Models\NoticeTag;
use App\Models\Construction;

it('creates a construction notice tag when a construction is created', function (): void {
    $construction = Construction::factory()->create([
        'title' => 'Rehabilitacion Fachada',
        'slug' => 'rehabilitacion-fachada',
    ]);

    $tag = NoticeTag::query()->where('slug', 'obra-' . $construction->slug)->first();

    expect($tag)->not->toBeNull()
        ->and($tag?->name_eu)->toBe('Obra: Rehabilitacion Fachada')
        ->and($tag?->name_es)->toBe('Obra: Rehabilitacion Fachada');
});
