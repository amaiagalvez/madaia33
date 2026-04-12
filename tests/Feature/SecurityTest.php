<?php

// Feature: community-web, Task 13: Legal pages, SEO, and security
// Validates: Requirements 16.4, 16.5, 17.3

use App\Models\User;
use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

// ─────────────────────────────────────────────────────────────────────────────
// HTTP security headers
// ─────────────────────────────────────────────────────────────────────────────

it('security headers are present on public routes', function () {
    $response = $this->get(route(SupportedLocales::routeName('home', SupportedLocales::DEFAULT)));

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('security headers are present on admin routes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

// ─────────────────────────────────────────────────────────────────────────────
// Sitemap and robots.txt
// ─────────────────────────────────────────────────────────────────────────────

it('sitemap.xml is accessible and contains public URLs', function (string $locale) {
    $response = $this->get(route('sitemap'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<urlset', false)
        ->assertSee(route(SupportedLocales::routeName('home', $locale)), false);
})->with('supported_locales');

it('robots.txt is accessible and contains correct directives', function () {
    $response = $this->get(route('robots'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *', false)
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Sitemap:', false);
});
