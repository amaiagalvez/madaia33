<?php

// Feature: community-web, Task 8: Admin panel authentication
// Validates: Requirements 6.5, 6.6

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

// ─────────────────────────────────────────────────────────────────────────────
// Login correcto → redirige al dashboard admin
// Validates: Requirement 6.5
// ─────────────────────────────────────────────────────────────────────────────

it('login with correct credentials redirects to admin dashboard', function () {
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
// Invalid login -> shows error and denies access
// Validates: Requirement 6.6
// ─────────────────────────────────────────────────────────────────────────────

it('login with incorrect credentials shows error and denies access', function () {
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
// Accessing /admin without authentication -> redirects to login
// Validates: Requirement 6.5
// ─────────────────────────────────────────────────────────────────────────────

it('accessing /admin without authentication redirects to login', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});
