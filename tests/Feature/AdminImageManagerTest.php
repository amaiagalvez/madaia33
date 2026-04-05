<?php

// Feature: community-web, Tarea 10: Panel de administración — Gestión de imágenes
// Valida: Requisitos 6.2, 6.3, 3.2, 3.3

use App\Models\User;
use App\Models\Image;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

// ─────────────────────────────────────────────────────────────────────────────
// Subir imagen → aparece en galería pública
// Valida: Requisitos 6.2, 3.2
// ─────────────────────────────────────────────────────────────────────────────

it('subir imagen la hace aparecer en la galería pública', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('foto.jpg', 100, 100);

    Livewire::actingAs($user)
        ->test('admin-image-manager')
        ->set('photo', $file)
        ->set('altEu', 'Argazkia')
        ->set('altEs', 'Imagen')
        ->call('uploadImage');

    $image = Image::first();
    expect($image)->not->toBeNull();
    expect($image->alt_text_eu)->toBe('Argazkia');

    Storage::disk('public')->assertExists($image->path);

    $gallery = Livewire::test('image-gallery');
    expect($gallery->images->pluck('id'))->toContain($image->id);
});

// ─────────────────────────────────────────────────────────────────────────────
// Eliminar imagen → desaparece de galería pública
// Valida: Requisitos 6.2, 3.3
// ─────────────────────────────────────────────────────────────────────────────

it('eliminar imagen la quita de la galería pública', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('foto.png', 100, 100);

    // Upload first
    Livewire::actingAs($user)
        ->test('admin-image-manager')
        ->set('photo', $file)
        ->call('uploadImage');

    $image = Image::first();
    expect($image)->not->toBeNull();

    // Delete with confirmation
    Livewire::actingAs($user)
        ->test('admin-image-manager')
        ->call('confirmDelete', $image->id)
        ->call('deleteImage');

    expect(Image::find($image->id))->toBeNull();

    $gallery = Livewire::test('image-gallery');
    expect($gallery->images->pluck('id'))->not->toContain($image->id);
});
