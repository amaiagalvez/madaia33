<?php

use App\Models\User;

test('registration screen is not available', function () {
    test()->get('/register')->assertNotFound();
});

test('new users can not register from the public routes', function () {
    $response = test()->post('/register', [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});
