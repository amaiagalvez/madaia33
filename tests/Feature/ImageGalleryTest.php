<?php

// Feature: community-web, Tarea 5: Parte pública — Galería de imágenes
// Valida: Requisitos 3.1, 3.2, 3.3, 3.4, 3.7, 4.1, 4.2, 4.4, 4.5, 4.6

use App\Models\Image;
use Livewire\Livewire;
use Illuminate\Support\Facades\App;

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
    App::setLocale('eu');

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

it('la galería pública es accesible', function () {
    $response = $this->get(route('gallery'));

    $response->assertOk();
});

it('las imágenes se ordenan por created_at descendente', function () {
    Image::factory()->count(3)->create();

    $component = Livewire::test('image-gallery');

    $dates = $component->images->pluck('created_at');
    for ($i = 0; $i < $dates->count() - 1; $i++) {
        expect($dates[$i]->gte($dates[$i + 1]))->toBeTrue();
    }
});
