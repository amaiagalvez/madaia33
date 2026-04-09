<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

test('login screen can be rendered', function () {
    $response = test()->get(route('login'));

    $response->assertOk();
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
    $user = User::factory()->create(['name' => '12345678Z']);

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
