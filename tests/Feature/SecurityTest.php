<?php

// Feature: community-web, Tarea 13: Páginas legales, SEO y seguridad
// Valida: Requisitos 16.4, 16.5, 17.3

use App\Models\User;

// ─────────────────────────────────────────────────────────────────────────────
// Cabeceras de seguridad HTTP
// ─────────────────────────────────────────────────────────────────────────────

it('las cabeceras de seguridad están presentes en rutas públicas', function () {
    $response = $this->get(route('home'));

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('las cabeceras de seguridad están presentes en rutas admin', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

// ─────────────────────────────────────────────────────────────────────────────
// Sitemap y robots.txt
// ─────────────────────────────────────────────────────────────────────────────

it('el sitemap.xml es accesible y contiene las URLs públicas', function () {
    $response = $this->get(route('sitemap'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<urlset', false)
        ->assertSee(route('home'), false);
});

it('el robots.txt es accesible y contiene las directivas correctas', function () {
    $response = $this->get(route('robots'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *', false)
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Sitemap:', false);
});
