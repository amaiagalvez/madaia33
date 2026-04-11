<?php

// Feature: community-web, Property 4: Bilingual accessor with correct fallback
// Validates: Requirements 2.4, 2.5

use App\Models\Image;
use App\Models\Notice;
use App\SupportedLocales;
use Illuminate\Support\Facades\App;

/**
 * Property 4: Bilingual accessor with correct fallback
 *
 * For any notice with content in one or both languages and any valid locale
 * the model accessor must return the active locale field
 * when it exists, or the other language field if the active locale field is empty or null,
 * never returning null when at least one language has content.
 *
 * Validates: Requirements 2.4, 2.5
 */
it('returns title in active locale when both languages exist', function () {
    $notice = new Notice([
        'title_eu' => 'Izenburua euskaraz',
        'title_es' => 'Título en castellano',
    ]);

    App::setLocale(SupportedLocales::BASQUE);
    expect($notice->title)->toBe('Izenburua euskaraz');

    App::setLocale(SupportedLocales::SPANISH);
    expect($notice->title)->toBe('Título en castellano');
});

it('falls back to the other language when active locale has no title', function () {
    // Solo tiene EU
    $noticeEuOnly = new Notice([
        'title_eu' => 'Izenburua euskaraz',
        'title_es' => null,
    ]);

    App::setLocale(SupportedLocales::SPANISH);
    expect($noticeEuOnly->title)->toBe('Izenburua euskaraz');

    // Solo tiene ES
    $noticeEsOnly = new Notice([
        'title_eu' => null,
        'title_es' => 'Título en castellano',
    ]);

    App::setLocale(SupportedLocales::BASQUE);
    expect($noticeEsOnly->title)->toBe('Título en castellano');
});

it('never returns null when at least one language has title', function () {
    $locales = SupportedLocales::all();

    foreach ($locales as $locale) {
        App::setLocale($locale);

        $noticeEuOnly = new Notice(['title_eu' => 'Izenburua', 'title_es' => null]);
        expect($noticeEuOnly->title)->not->toBeNull()->not->toBe('');

        $noticeEsOnly = new Notice(['title_eu' => null, 'title_es' => 'Título']);
        expect($noticeEsOnly->title)->not->toBeNull()->not->toBe('');
    }
});

it('returns content in active locale with correct fallback', function () {
    $notice = new Notice([
        'content_eu' => 'Edukia euskaraz',
        'content_es' => 'Contenido en castellano',
    ]);

    App::setLocale(SupportedLocales::BASQUE);
    expect($notice->content)->toBe('Edukia euskaraz');

    App::setLocale(SupportedLocales::SPANISH);
    expect($notice->content)->toBe('Contenido en castellano');

    // Fallback: solo EU disponible
    $noticeEuOnly = new Notice(['content_eu' => 'Edukia', 'content_es' => null]);
    App::setLocale(SupportedLocales::SPANISH);
    expect($noticeEuOnly->content)->toBe('Edukia');
});

it('returns image alt_text in active locale with correct fallback', function () {
    $image = new Image([
        'alt_text_eu' => 'Irudiaren deskribapena',
        'alt_text_es' => 'Descripción de la imagen',
    ]);

    App::setLocale(SupportedLocales::BASQUE);
    expect($image->alt_text)->toBe('Irudiaren deskribapena');

    App::setLocale(SupportedLocales::SPANISH);
    expect($image->alt_text)->toBe('Descripción de la imagen');

    // Fallback: solo EU disponible
    $imageEuOnly = new Image(['alt_text_eu' => 'Irudia', 'alt_text_es' => null]);
    App::setLocale(SupportedLocales::SPANISH);
    expect($imageEuOnly->alt_text)->toBe('Irudia');
});
