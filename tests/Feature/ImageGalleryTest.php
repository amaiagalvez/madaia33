<?php

// Feature: community-web, Tarea 5: Parte pública — Galería de imágenes
// Valida: Requisitos 3.1, 3.2, 3.3, 3.4, 3.7, 4.1, 4.2, 4.4, 4.5, 4.6

use App\Models\Image;
use Livewire\Livewire;
use App\SupportedLocales;
use Illuminate\Support\Facades\App;

dataset('supported_locales', SupportedLocales::all());

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 5: Round-trip de imagen (subir / eliminar)
// Valida: Requisitos 3.2, 3.3
// ─────────────────────────────────────────────────────────────────────────────

it('muestra las imágenes creadas en la galería pública', function () {
    $count = 3;
    Image::factory()->count($count)->create();

    $component = Livewire::test('image-gallery');

    expect($component->images)->toHaveCount($count);
});

it('no muestra imágenes eliminadas en la galería', function () {
    $image = Image::factory()->create(['alt_text_eu' => 'Argazkia']);
    $image->delete();

    $component = Livewire::test('image-gallery');

    expect($component->images)->toBeEmpty();
});

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 6: Alt text presente en todas las imágenes de la galería
// Valida: Requisito 3.4
// ─────────────────────────────────────────────────────────────────────────────

it('todas las imágenes tienen alt text en el locale activo', function () {
    App::setLocale(SupportedLocales::BASQUE);

    Image::factory()->count(3)->create();

    $component = Livewire::test('image-gallery');

    foreach ($component->images as $image) {
        expect($image->alt_text)->not->toBeEmpty();
    }
});

it('muestra todas las imágenes cuando no hay filtro activo', function () {
    $count = 3;
    Image::factory()->count($count)->create();

    $component = Livewire::test('image-gallery');

    expect($component->images)->toHaveCount($count);
});

// ─────────────────────────────────────────────────────────────────────────────
// Tests de ejemplo
// Valida: Requisitos 3.1, 3.7, 4.4
// ─────────────────────────────────────────────────────────────────────────────

it('muestra mensaje de vacío cuando no hay imágenes', function () {
    $component = Livewire::test('image-gallery');

    $component->assertSee(__('gallery.empty'));
});

it('la galería pública es accesible', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('gallery', $locale)));

    $response->assertOk();
    $response->assertSee('data-page-hero="gallery"', false);
})->with('supported_locales');

it('las imágenes se ordenan por created_at descendente', function () {
    Image::factory()->count(3)->create();

    $component = Livewire::test('image-gallery');

    $dates = $component->images->pluck('created_at');
    for ($i = 0; $i < $dates->count() - 1; $i++) {
        expect($dates[$i]->gte($dates[$i + 1]))->toBeTrue();
    }
});

it('renderiza la galería con grid responsive esperado', function (string $locale) {
    Image::factory()->count(2)->create();

    $response = test()->get(route(SupportedLocales::routeName('gallery', $locale)));

    $response->assertOk();
    $response->assertSee('grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4', false);
    $response->assertSee('data-gallery-grid', false);
})->with('supported_locales');

it('incluye controles avanzados del lightbox responsive', function (string $locale) {
    Image::factory()->count(1)->create();

    $response = test()->get(route(SupportedLocales::routeName('gallery', $locale)));

    $response->assertOk();
    $response->assertSee('window.innerHeight < window.innerWidth', false);
    $response->assertSee("isLandscape ? 'max-h-[85vh]' : 'max-h-[90vh]'", false);
    $response->assertSee("document.body.style.overflow = 'hidden'", false);
    $response->assertSee('data-lightbox-close', false);
    $response->assertSee('min-h-11 min-w-11', false);
    $response->assertSee('@touchmove="handleTouchMove($event)"', false);
})->with('supported_locales');

it('filtra la galería por etiqueta historia', function () {
    $historyImage = Image::factory()->history()->create();
    Image::factory()->madaia()->create();

    $component = Livewire::test('image-gallery')
        ->call('setTagFilter', 'historia');

    expect($component->images)->toHaveCount(1)
        ->and($component->images->first()->id)->toBe($historyImage->id);
});

it('filtra la galería por etiqueta madaia', function () {
    Image::factory()->history()->create();
    $madaiaImage = Image::factory()->madaia()->create();

    $component = Livewire::test('image-gallery')
        ->call('setTagFilter', 'madaia');

    expect($component->images)->toHaveCount(1)
        ->and($component->images->first()->id)->toBe($madaiaImage->id);
});
