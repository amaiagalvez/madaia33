<?php

// Feature: community-web, Tarea 8: Autenticación del panel de administración
// Valida: Requisitos 6.5, 6.6

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

// ─────────────────────────────────────────────────────────────────────────────
// Login correcto → redirige al dashboard admin
// Valida: Requisito 6.5
// ─────────────────────────────────────────────────────────────────────────────

it('login con credenciales correctas redirige al dashboard admin', function () {
    $user = User::factory()->create();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();
});

// ─────────────────────────────────────────────────────────────────────────────
// Login incorrecto → muestra error y no concede acceso
// Valida: Requisito 6.6
// ─────────────────────────────────────────────────────────────────────────────

it('login con credenciales incorrectas muestra error y no concede acceso', function () {
    $user = User::factory()->create();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'contraseña-incorrecta',
        ])
        ->assertSessionHasErrors();

    $this->assertGuest();
});

// ─────────────────────────────────────────────────────────────────────────────
// Acceso a /admin sin autenticación → redirige al login
// Valida: Requisito 6.5
// ─────────────────────────────────────────────────────────────────────────────

it('acceder a /admin sin autenticación redirige al login', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});
