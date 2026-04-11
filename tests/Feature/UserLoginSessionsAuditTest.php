<?php

use App\Models\User;
use App\Models\UserLoginSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

it('records login and logout timestamps in user login sessions audit', function () {
    $user = User::factory()->create([
        'email' => 'audit-user@example.com',
        'password' => Hash::make('password123'),
    ]);

    Auth::login($user);

    test()->assertAuthenticatedAs($user);

    $loginSession = UserLoginSession::query()
        ->where('user_id', $user->id)
        ->latest('id')
        ->first();

    expect($loginSession)->not->toBeNull()
        ->and($loginSession?->logged_in_at)->not->toBeNull()
        ->and($loginSession?->logged_out_at)->toBeNull();

    Auth::logout();

    test()->assertGuest();

    $loginSession?->refresh();

    expect($loginSession?->logged_out_at)->not->toBeNull();
});
