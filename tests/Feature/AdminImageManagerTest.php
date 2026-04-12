<?php

// Feature: community-web, Task 10: Admin panel — Image management
// Validates: Requirements 6.2, 6.3, 3.2, 3.3

use App\Models\User;
use App\Models\Image;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

// ─────────────────────────────────────────────────────────────────────────────
// Upload image -> appears in public gallery
// Validates: Requirements 6.2, 3.2
// ─────────────────────────────────────────────────────────────────────────────

it('uploading an image makes it appear in public gallery', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('foto.jpg', 100, 100);

    Livewire::actingAs($user)
        ->test('admin-image-manager')
        ->set('photo', $file)
        ->set('altEu', 'Argazkia')
        ->set('altEs', 'Imagen')
        ->set('tag', Image::TAG_MADAIA)
        ->call('uploadImage');

    $image = Image::first();
    expect($image)->not->toBeNull();
    expect($image->alt_text_eu)->toBe('Argazkia');
    expect($image->tag)->toBe(Image::TAG_MADAIA);

    expect(Storage::disk('public')->exists($image->path))->toBeTrue();

    $gallery = Livewire::test('image-gallery');
    expect($gallery->images->pluck('id'))->toContain($image->id);
});

// ─────────────────────────────────────────────────────────────────────────────
// Delete image -> disappears from public gallery
// Validates: Requirements 6.2, 3.3
// ─────────────────────────────────────────────────────────────────────────────

it('deleting an image removes it from public gallery', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('foto.png', 100, 100);

    // Upload first
    Livewire::actingAs($user)
        ->test('admin-image-manager')
        ->set('photo', $file)
        ->set('tag', Image::TAG_HISTORY)
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

it('shows alt texts in both languages and tag in admin images list', function () {
    $user = User::factory()->create();
    $image = Image::factory()->create([
        'alt_text_eu' => 'Atari nagusia',
        'alt_text_es' => 'Portal principal',
        'tag' => Image::TAG_HISTORY,
    ]);

    Livewire::actingAs($user)
        ->test('admin-image-manager')
        ->assertSee('Atari nagusia')
        ->assertSee('Portal principal')
        ->assertSee(__('gallery.filter.history'))
        ->assertSeeHtml('data-admin-form-file-input')
        ->assertSeeHtml('data-admin-field="single-radio-pills"')
        ->assertSee('data-image-alt-eu="' . $image->id . '"', false)
        ->assertSee('data-image-alt-es="' . $image->id . '"', false)
        ->assertSee('data-image-tag="' . $image->id . '"', false);
});
