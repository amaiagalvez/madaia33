<?php

use App\Models\Notice;
use App\Models\NoticeTag;
use App\SupportedLocales;
use App\Models\Construction;
use App\Models\NoticeDocument;
use App\Models\NoticeDocumentDownload;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('keeps notice document download without soft deletes and without timestamps', function (): void {
    $model = new NoticeDocumentDownload;

    expect($model->timestamps)->toBeFalse()
        ->and(in_array(SoftDeletes::class, class_uses_recursive($model), true))->toBeFalse();
});

it('generates a uuid token automatically for notice documents', function (): void {
    $notice = Notice::factory()->create();

    $document = NoticeDocument::query()->create([
        'notice_id' => $notice->id,
        'filename' => 'presupuesto.pdf',
        'path' => 'notices/presupuesto.pdf',
        'mime_type' => 'application/pdf',
        'size_bytes' => 2048,
        'is_public' => false,
    ]);

    expect($document->token)->not->toBeNull()
        ->and($document->token)->toMatch('/^[0-9a-fA-F-]{36}$/');
});

it('applies expected casts and active scope on construction model', function (): void {
    $model = new Construction;

    expect($model->getCasts())
        ->toMatchArray([
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ]);

    $active = Construction::factory()->create([
        'starts_at' => today()->subDay(),
        'ends_at' => today()->addDay(),
        'is_active' => true,
    ]);

    Construction::factory()->create([
        'starts_at' => today()->addDay(),
        'ends_at' => today()->addDays(10),
        'is_active' => true,
    ]);

    Construction::factory()->create([
        'starts_at' => today()->subDays(10),
        'ends_at' => today()->subDay(),
        'is_active' => true,
    ]);

    Construction::factory()->create([
        'starts_at' => today()->subDays(2),
        'ends_at' => today()->addDays(2),
        'is_active' => false,
    ]);

    $activeIds = Construction::query()->active()->pluck('id')->all();

    expect($activeIds)->toContain($active->id)
        ->and(count($activeIds))->toBe(1);
});

it('resolves notice tag bilingual accessor with fallback', function (): void {
    app()->setLocale(SupportedLocales::SPANISH);

    $tag = NoticeTag::factory()->create([
        'name_eu' => 'Obra nagusia',
        'name_es' => null,
    ]);

    expect($tag->name)->toBe('Obra nagusia');

    app()->setLocale(SupportedLocales::DEFAULT);
});
