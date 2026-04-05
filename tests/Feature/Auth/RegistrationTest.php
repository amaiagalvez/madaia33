<?php

use Laravel\Fortify\Features;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('/admin');

    $this->assertAuthenticated();
});
