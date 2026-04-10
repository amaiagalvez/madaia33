<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use Laravel\Fortify\Features;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

test('login screen can be rendered', function () {
    $response = test()->get(route('login'));

    $response->assertRedirect(route('private.eu'));
});

test('login screen redirect keeps the current locale', function () {
    test()->get(route('private.es'));

    $response = test()->get(route('login'));

    $response->assertRedirect(route('private.es'));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin');

    test()->assertAuthenticated();
});

test('users can authenticate with dni as login identifier', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::PROPERTY_OWNER);

    Owner::factory()->for($user)->create([
        'coprop1_dni' => '12345678Z',
    ]);

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => '12345678Z',
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin');

    test()->assertAuthenticated();
});

test('property owner users can authenticate with coproprietary emails', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::PROPERTY_OWNER);

    Owner::factory()->for($user)->create([
        'coprop1_email' => 'owner1@example.com',
        'coprop2_email' => 'owner2@example.com',
    ]);

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => 'owner1@example.com',
            'password' => 'password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin');

    test()->assertAuthenticated();

    test()->post(route('logout'));

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => 'owner2@example.com',
            'password' => 'password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin');

    test()->assertAuthenticatedAs($user);
});

test('users without propietaria role can not authenticate with dni or coproprietary emails', function () {
    $user = User::factory()->create();

    Owner::factory()->for($user)->create([
        'coprop1_dni' => '12345678Z',
        'coprop1_email' => 'owner1@example.com',
    ]);

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => '12345678Z',
            'password' => 'password',
        ])
        ->assertSessionHasErrorsIn('email');

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => 'owner1@example.com',
            'password' => 'password',
        ])
        ->assertSessionHasErrorsIn('email');

    test()->assertGuest();
});

test('users without owner can not authenticate with dni as login identifier', function () {
    User::factory()->create();

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => '12345678Z',
            'password' => 'password',
        ]);

    $response->assertSessionHasErrorsIn('email');

    test()->assertGuest();
});

test('inactive users can not authenticate', function () {
    $user = User::factory()->create(['is_active' => false]);

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response->assertSessionHasErrorsIn('email');

    test()->assertGuest();
});

test('inactive users with owner can not authenticate with dni', function () {
    $user = User::factory()->create(['is_active' => false]);

    Owner::factory()->for($user)->create([
        'coprop1_dni' => '12345678Z',
    ]);

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => '12345678Z',
            'password' => 'password',
        ]);

    $response->assertSessionHasErrorsIn('email');

    test()->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrorsIn('email');

    test()->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    test()->skipUnlessFortifyFeature(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response->assertRedirect(route('two-factor.login'));
    test()->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('logout'));

    $response->assertRedirect(route('root'));

    test()->assertGuest();
});

test('front header shows authenticated user name and logout button', function () {
    $user = User::factory()->create([
        'name' => 'Frontend User',
    ]);

    test()->actingAs($user)
        ->get(route('home.eu'))
        ->assertOk()
        ->assertSee('Frontend User')
        ->assertSee(__('admin.logout'));
});
