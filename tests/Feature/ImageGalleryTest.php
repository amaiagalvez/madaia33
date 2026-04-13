<?php

// Feature: community-web, Task 5: Public area — Image gallery
// Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.7, 4.1, 4.2, 4.4, 4.5, 4.6

use App\Models\Image;
use Livewire\Livewire;
use App\SupportedLocales;
use Illuminate\Support\Facades\App;

dataset('supported_locales', SupportedLocales::all());

// ─────────────────────────────────────────────────────────────────────────────
// Property 5: Image round trip (upload / delete)
// Validates: Requirements 3.2, 3.3
// ─────────────────────────────────────────────────────────────────────────────

it('shows created images in public gallery', function () {
    $count = 3;
    Image::factory()->count($count)->create();

    $component = Livewire::test('image-gallery');

    expect($component->images)->toHaveCount($count);
});

it('does not show deleted images in gallery', function () {
    $image = Image::factory()->create(['alt_text_eu' => 'Argazkia']);
    $image->delete();

    $component = Livewire::test('image-gallery');

    expect($component->images)->toBeEmpty();
});

// ─────────────────────────────────────────────────────────────────────────────
// Property 6: Alt text present in all gallery images
// Validates: Requirement 3.4
// ─────────────────────────────────────────────────────────────────────────────

it('all images have alt text in active locale', function () {
    App::setLocale(SupportedLocales::BASQUE);

    Image::factory()->count(3)->create();

    $component = Livewire::test('image-gallery');

    foreach ($component->images as $image) {
        expect($image->alt_text)->not->toBeEmpty();
    }
});

it('shows all images when no filter is active', function () {
    $count = 3;
    Image::factory()->count($count)->create();

    $component = Livewire::test('image-gallery');

    expect($component->images)->toHaveCount($count);
});

// ─────────────────────────────────────────────────────────────────────────────
// Example tests
// Validates: Requirements 3.1, 3.7, 4.4
// ─────────────────────────────────────────────────────────────────────────────

it('shows empty-state message when there are no images', function () {
    $component = Livewire::test('image-gallery');

    $component->assertSee(__('gallery.empty'));
});

it('public gallery is accessible', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('gallery', $locale)));

    $response->assertOk();
    $response->assertSee('data-page-hero="gallery"', false);
})->with('supported_locales');

it('images are ordered by created_at descending', function () {
    Image::factory()->count(3)->create();

    $component = Livewire::test('image-gallery');

    $dates = $component->images->pluck('created_at');
    for ($i = 0; $i < $dates->count() - 1; $i++) {
        expect($dates[$i]->gte($dates[$i + 1]))->toBeTrue();
    }
});

it('renders gallery with expected responsive grid', function (string $locale) {
    Image::factory()->count(2)->create();

    $response = test()->get(route(SupportedLocales::routeName('gallery', $locale)));

    $response->assertOk();
    $response->assertSee('grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4', false);
    $response->assertSee('data-gallery-grid', false);
})->with('supported_locales');

it('includes advanced responsive lightbox controls', function (string $locale) {
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

it('filters gallery by history tag', function () {
    $historyImage = Image::factory()->history()->create();
    Image::factory()->comunity()->create();

    $component = Livewire::test('image-gallery')
        ->call('setTagFilter', 'history');

    expect($component->images)->toHaveCount(1)
        ->and($component->images->first()->id)->toBe($historyImage->id);
});

it('filters gallery by comunity tag', function () {
    Image::factory()->history()->create();
    $comunityImage = Image::factory()->comunity()->create();

    $component = Livewire::test('image-gallery')
        ->call('setTagFilter', 'comunity');

    expect($component->images)->toHaveCount(1)
        ->and($component->images->first()->id)->toBe($comunityImage->id);
});

it('resets filter when tag is invalid', function () {
    Image::factory()->history()->create();
    Image::factory()->comunity()->create();

    $component = Livewire::test('image-gallery')
        ->call('setTagFilter', 'history')
        ->call('setTagFilter', 'invalid-tag');

    expect($component->activeTag)->toBe('')
        ->and($component->images)->toHaveCount(2);
});
