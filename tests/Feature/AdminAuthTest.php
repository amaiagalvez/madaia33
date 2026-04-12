<?php

// Feature: community-web, Task 8: Admin panel authentication
// Validates: Requirements 6.5, 6.6

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// Login correcto → redirige al dashboard admin
// Validates: Requirement 6.5
// ─────────────────────────────────────────────────────────────────────────────

it('login with correct credentials redirects to admin dashboard', function () {
    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('admin.dashboard'));

    test()->assertAuthenticated();
});

// ─────────────────────────────────────────────────────────────────────────────
// Invalid login -> shows error and denies access
// Validates: Requirement 6.6
// ─────────────────────────────────────────────────────────────────────────────

it('login with incorrect credentials shows error and denies access', function () {
    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'contraseña-incorrecta',
        ])
        ->assertSessionHasErrors();

    test()->assertGuest();
});

// ─────────────────────────────────────────────────────────────────────────────
// Accessing /admin without authentication -> redirects to login
// Validates: Requirement 6.5
// ─────────────────────────────────────────────────────────────────────────────

it('accessing /admin without authentication redirects to login', function () {
    test()->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('redirects delegated vote user without admin roles to home when accessing admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::DELEGATED_VOTE);

    test()->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('home.eu'));
});

it('redirects property owner user without admin roles to home when accessing admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::PROPERTY_OWNER);

    test()->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('home.eu'));
});
