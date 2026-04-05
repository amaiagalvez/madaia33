<?php

// Feature: community-web, Propiedad 4: Accessor bilingüe con fallback correcto
// Valida: Requisitos 2.4, 2.5

use App\Models\Image;
use App\Models\Notice;
use Illuminate\Support\Facades\App;

/**
 * Propiedad 4: Accessor bilingüe con fallback correcto
 *
 * Para cualquier aviso con contenido en uno o ambos idiomas y cualquier locale
 * activo válido, el accessor del modelo debe devolver el campo del locale activo
 * si existe, o el campo del otro idioma si el del locale activo está vacío o nulo,
 * nunca devolviendo null cuando al menos un idioma tiene contenido.
 *
 * Valida: Requisitos 2.4, 2.5
 */
it('devuelve el título en el locale activo cuando ambos idiomas existen', function () {
    $notice = new Notice([
        'title_eu' => 'Izenburua euskaraz',
        'title_es' => 'Título en castellano',
    ]);

    App::setLocale('eu');
    expect($notice->title)->toBe('Izenburua euskaraz');

    App::setLocale('es');
    expect($notice->title)->toBe('Título en castellano');
});

it('hace fallback al otro idioma cuando el locale activo no tiene título', function () {
    // Solo tiene EU
    $noticeEuOnly = new Notice([
        'title_eu' => 'Izenburua euskaraz',
        'title_es' => null,
    ]);

    App::setLocale('es');
    expect($noticeEuOnly->title)->toBe('Izenburua euskaraz');

    // Solo tiene ES
    $noticeEsOnly = new Notice([
        'title_eu' => null,
        'title_es' => 'Título en castellano',
    ]);

    App::setLocale('eu');
    expect($noticeEsOnly->title)->toBe('Título en castellano');
});

it('nunca devuelve null cuando al menos un idioma tiene título', function () {
    $locales = ['eu', 'es'];

    foreach ($locales as $locale) {
        App::setLocale($locale);

        $noticeEuOnly = new Notice(['title_eu' => 'Izenburua', 'title_es' => null]);
        expect($noticeEuOnly->title)->not->toBeNull()->not->toBe('');

        $noticeEsOnly = new Notice(['title_eu' => null, 'title_es' => 'Título']);
        expect($noticeEsOnly->title)->not->toBeNull()->not->toBe('');
    }
});

it('devuelve el contenido en el locale activo con fallback correcto', function () {
    $notice = new Notice([
        'content_eu' => 'Edukia euskaraz',
        'content_es' => 'Contenido en castellano',
    ]);

    App::setLocale('eu');
    expect($notice->content)->toBe('Edukia euskaraz');

    App::setLocale('es');
    expect($notice->content)->toBe('Contenido en castellano');

    // Fallback: solo EU disponible
    $noticeEuOnly = new Notice(['content_eu' => 'Edukia', 'content_es' => null]);
    App::setLocale('es');
    expect($noticeEuOnly->content)->toBe('Edukia');
});

it('devuelve el alt_text de imagen en el locale activo con fallback correcto', function () {
    $image = new Image([
        'alt_text_eu' => 'Irudiaren deskribapena',
        'alt_text_es' => 'Descripción de la imagen',
    ]);

    App::setLocale('eu');
    expect($image->alt_text)->toBe('Irudiaren deskribapena');

    App::setLocale('es');
    expect($image->alt_text)->toBe('Descripción de la imagen');

    // Fallback: solo EU disponible
    $imageEuOnly = new Image(['alt_text_eu' => 'Irudia', 'alt_text_es' => null]);
    App::setLocale('es');
    expect($imageEuOnly->alt_text)->toBe('Irudia');
});
