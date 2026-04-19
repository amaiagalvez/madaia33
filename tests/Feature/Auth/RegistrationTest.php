<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;

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

test('fortify create action assigns a unique 9 digit code as the initial password', function () {
    $action = app(CreateNewUser::class);

    $firstUser = $action->create([
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'language' => 'eu',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $secondUser = $action->create([
        'name' => 'Jane Doe',
        'email' => 'test2@example.com',
        'language' => 'es',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    expect($firstUser->code)->toMatch('/^\d{9}$/')
        ->and(Hash::check((string) $firstUser->code, (string) $firstUser->password))->toBeTrue()
        ->and($secondUser->code)->toMatch('/^\d{9}$/')
        ->and($secondUser->code)->not->toBe($firstUser->code)
        ->and(Hash::check((string) $secondUser->code, (string) $secondUser->password))->toBeTrue();
});
