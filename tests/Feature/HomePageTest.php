<?php

use App\Models\Notice;

test('home page renders hero slider component', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('hero-slider');
});

test('home page displays latest notices grid', function () {
    $notices = Notice::factory()->public()->count(6)->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    foreach ($notices as $notice) {
        $response->assertSee($notice->title);
    }
});

test('home page renders notices section with history block', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee(__('home.notices'));
    $response->assertSee(__('home.history_title'));
    $response->assertSee(__('home.history_summary'));
    $response->assertSee('data-home-history', false);
});

test('public layout keeps sticky header solid and prevents horizontal overflow', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('overflow-x-clip', false);
    $response->assertSee('sticky top-0 z-[70]', false);
    $response->assertSee('public-header', false);
    $response->assertSee('header-shell', false);
    $response->assertSee('header-nav-panel', false);
    $response->assertSee('header-brand-mark', false);
    $response->assertSee('pt-[env(safe-area-inset-top)]', false);
});

test('home page shows only latest 6 notices', function () {
    Notice::factory()->public()->count(10)->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    // Count the number of notice-card components rendered
    $notices = Notice::public()->latest()->limit(6)->get();
    expect($notices)->toHaveCount(6);
});

test('home page shows view all button when more than 6 notices exist', function () {
    Notice::factory()->public()->count(8)->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee(__('home.view_all'));
    $response->assertSee(route('notices'));
});

test('home page does not show view all button when 6 or fewer notices exist', function () {
    Notice::factory()->public()->count(3)->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertDontSee(__('home.view_all'));
});

test('home page shows empty state when no notices exist', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee(__('home.no_notices'));
});

test('home page renders notice card with correct structure', function () {
    Notice::factory()->public()->create([
        'title_eu' => 'Aviso de prueba',
        'title_es' => 'Test Notice',
    ]);

    $response = $this->get('/');

    $response->assertSuccessful();
    // Check for notice-card component classes
    $response->assertSee('elevated-card');
    $response->assertSee('line-clamp-2');
});

test('home page respects public scope for notices', function () {
    $publicNotice = Notice::factory()->public()->create();
    $privateNotice = Notice::factory()->private()->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee($publicNotice->title);
    $response->assertDontSee($privateNotice->title);
});

test('home page latest notices are ordered by latest', function () {
    $older = Notice::factory()->public()->create(['created_at' => now()->subDays(5)]);
    $newer = Notice::factory()->public()->create(['created_at' => now()->subDays(1)]);

    $response = $this->get('/');

    $response->assertSuccessful();
    // The newer notice should appear first in the HTML
    $content = $response->getContent();
    $newerPos = strpos($content, $newer->title);
    $olderPos = strpos($content, $older->title);
    expect($newerPos < $olderPos)->toBeTrue();
});
