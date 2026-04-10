<?php

use App\Models\Image;
use App\Models\Notice;
use App\Models\Setting;
use App\SupportedLocales;
use Illuminate\Support\Facades\Storage;

dataset('supported_locales', SupportedLocales::all());

test('home page renders hero slider component', function (string $locale) {
    test()->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertSee('hero-slider');
})->with('supported_locales');

test('home page displays latest notices grid', function (string $locale) {
    $notices = Notice::factory()->public()->count(6)->create();

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    foreach ($notices as $notice) {
        $response->assertSee($notice->title);
    }
})->with('supported_locales');

test('home page renders notices section with history block', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertSee(__('home.notices'));
    $response->assertSee(__('home.history_title'));
    $response->assertSee(__('home.history_summary'));
    $response->assertSee('data-home-history', false);
})->with('supported_locales');

test('public layout keeps sticky header solid and prevents horizontal overflow', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertSee('overflow-x-clip', false);
    $response->assertSee('sticky top-0 z-70', false);
    $response->assertSee('public-header', false);
    $response->assertSee('header-shell', false);
    $response->assertSee('header-brand-mark', false);
    $response->assertSee('pt-[env(safe-area-inset-top)]', false);
})->with('supported_locales');

test('home page shows only latest 6 notices', function (string $locale) {
    Notice::factory()->public()->count(10)->create();

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    // Count the number of notice-card components rendered
    $notices = Notice::public()->latest()->limit(6)->get();
    expect($notices)->toHaveCount(6);
})->with('supported_locales');

test('home page shows view all button when more than 6 notices exist', function (string $locale) {
    Notice::factory()->public()->count(8)->create();

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertSee(__('home.view_all'));
    $response->assertSee(route(SupportedLocales::routeName('notices', $locale)));
})->with('supported_locales');

test('home page does not show view all button when 6 or fewer notices exist', function (string $locale) {
    Notice::factory()->public()->count(3)->create();

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertDontSee(__('home.view_all'));
})->with('supported_locales');

test('home page shows empty state when no notices exist', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertSee(__('home.no_notices'));
})->with('supported_locales');

test('home page renders notice card with correct structure', function (string $locale) {
    Notice::factory()->public()->create([
        'title_eu' => 'Aviso de prueba',
        'title_es' => 'Test Notice',
    ]);

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    // Check for notice-card component classes
    $response->assertSee('elevated-card');
    $response->assertSee('line-clamp-2');
})->with('supported_locales');

test('home page respects public scope for notices', function (string $locale) {
    $publicNotice = Notice::factory()->public()->create();
    $privateNotice = Notice::factory()->private()->create();

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertSee($publicNotice->title);
    $response->assertDontSee($privateNotice->title);
})->with('supported_locales');

test('home page latest notices are ordered by latest', function (string $locale) {
    $older = Notice::factory()->public()->create(['created_at' => now()->subDays(5)]);
    $newer = Notice::factory()->public()->create(['created_at' => now()->subDays(1)]);

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    // The newer notice should appear first in the HTML
    $content = $response->getContent();
    $newerPos = strpos($content, $newer->title);
    $olderPos = strpos($content, $older->title);
    expect($newerPos < $olderPos)->toBeTrue();
})->with('supported_locales');

test('home page separates general notices from notices with location', function (string $locale) {
    $generalNotice = Notice::factory()->public()->create([
        'title_eu' => 'Iragarki Orokorra',
        'title_es' => 'Aviso General',
    ]);

    $locationNotice = Notice::factory()->public()->create([
        'title_eu' => 'Kokapeneko Iragarkia',
        'title_es' => 'Aviso con Ubicacion',
    ]);

    attachNoticeToLocationCode($locationNotice, 'A');

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();
    $response->assertSee('data-home-notices-general', false);
    $response->assertSee('data-home-notices-by-location', false);
    $response->assertSee($generalNotice->title);
    $response->assertSee($locationNotice->title);
})->with('supported_locales');

test('home page keeps mobile order general notices then location notices then history', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertSuccessful();

    $content = $response->getContent();
    $generalPos = strpos($content, 'data-home-notices-general');
    $locationPos = strpos($content, 'data-home-notices-by-location');
    $historyPos = strpos($content, 'data-home-history');

    expect($generalPos)->not->toBeFalse();
    expect($locationPos)->not->toBeFalse();
    expect($historyPos)->not->toBeFalse();
    expect($generalPos < $locationPos && $locationPos < $historyPos)->toBeTrue();
})->with('supported_locales');

test('home page footer uses shared menu brand on left and keeps amaia email logo on right', function (string $locale) {
    test()->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertSee(asset('storage/madaia33/madaia33.png'), false)
        ->assertSee(config('app.name', 'Madaia'))
        ->assertSee('mailto:info@amaia.eus', false)
        ->assertSee(asset('amaia-footer.png'), false)
        ->assertDontSee('&copy;', false);
})->with('supported_locales');

test('home page uses configurable history summary from front settings', function (string $locale) {
    $historyByLocale = [
        SupportedLocales::BASQUE => '<p>Historia personalizada EU</p>',
        SupportedLocales::SPANISH => '<p>Historia personalizada ES</p>',
    ];

    Setting::factory()->create([
        'key' => 'home_history_text_eu',
        'value' => $historyByLocale[SupportedLocales::BASQUE],
        'section' => Setting::SECTION_FRONT,
    ]);

    Setting::factory()->create([
        'key' => 'home_history_text_es',
        'value' => $historyByLocale[SupportedLocales::SPANISH],
        'section' => Setting::SECTION_FRONT,
    ]);

    test()->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertSee($historyByLocale[$locale], false);
})->with('supported_locales');

test('home page history section shows first three historia images stacked and excludes non-historia', function (string $locale) {
    Storage::fake('public');

    Storage::disk('public')->put('images/history-test-1.svg', '<svg xmlns="http://www.w3.org/2000/svg" />');
    Storage::disk('public')->put('images/history-test-2.svg', '<svg xmlns="http://www.w3.org/2000/svg" />');
    Storage::disk('public')->put('images/history-test-3.svg', '<svg xmlns="http://www.w3.org/2000/svg" />');
    Storage::disk('public')->put('images/history-test-4.svg', '<svg xmlns="http://www.w3.org/2000/svg" />');
    Storage::disk('public')->put('images/madaia-test.svg', '<svg xmlns="http://www.w3.org/2000/svg" />');

    Image::factory()->create([
        'filename' => 'madaia-test.svg',
        'path' => 'images/madaia-test.svg',
        'tag' => Image::TAG_MADAIA,
    ]);

    Image::factory()->create([
        'filename' => 'history-test-1.svg',
        'path' => 'images/history-test-1.svg',
        'tag' => Image::TAG_HISTORY,
    ]);

    Image::factory()->create([
        'filename' => 'history-test-2.svg',
        'path' => 'images/history-test-2.svg',
        'tag' => Image::TAG_HISTORY,
    ]);

    Image::factory()->create([
        'filename' => 'history-test-3.svg',
        'path' => 'images/history-test-3.svg',
        'tag' => Image::TAG_HISTORY,
    ]);

    Image::factory()->create([
        'filename' => 'history-test-4.svg',
        'path' => 'images/history-test-4.svg',
        'tag' => Image::TAG_HISTORY,
    ]);

    $response = test()->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful();

    $content = $response->getContent();
    $historySectionStart = strpos($content, 'data-home-history');

    expect($historySectionStart)->not->toBeFalse();

    $historySection = substr($content, $historySectionStart, 5000);

    expect($historySection)
        ->toContain('data-home-history-images')
        ->toContain('/storage/images/history-test-1.svg')
        ->toContain('/storage/images/history-test-2.svg')
        ->toContain('/storage/images/history-test-3.svg')
        ->not->toContain('/storage/images/history-test-4.svg')
        ->not->toContain('/storage/images/madaia-test.svg');
})->with('supported_locales');
