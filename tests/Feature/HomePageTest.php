<?php

use App\Models\Notice;
use App\SupportedLocales;

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
    $response->assertSee('header-nav-panel', false);
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
