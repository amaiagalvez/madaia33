<?php

use App\Models\Notice;
use App\SupportedLocales;
use App\Livewire\PublicNotices;
use Illuminate\Support\Facades\App;

test('livewire public notices reports missing translation when active locale fields are empty', function () {
    App::setLocale(SupportedLocales::SPANISH);

    $notice = Notice::factory()->public()->euOnly()->make();
    $component = app(PublicNotices::class);

    expect($component->hasTranslation($notice))->toBeFalse();
});

test('livewire public notices reports translation when active locale has title or content', function () {
    App::setLocale(SupportedLocales::SPANISH);

    $notice = Notice::factory()->public()->make([
        'title_eu' => 'Izenburua',
        'title_es' => 'Titulo',
        'content_eu' => 'Edukia',
        'content_es' => null,
    ]);

    $component = app(PublicNotices::class);

    expect($component->hasTranslation($notice))->toBeTrue();
});
