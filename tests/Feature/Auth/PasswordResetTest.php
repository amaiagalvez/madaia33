<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    test()->skipUnlessFortifyFeature(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = test()->get(route('password.request'));

    $response->assertOk()
        ->assertSee(__('admin.password_reset.request_title'))
        ->assertSee(__('admin.password_reset.request_description'));
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = test()->get(route('password.reset', $notification->token));

        $response->assertOk()
            ->assertSee(__('admin.password_reset.reset_title'))
            ->assertSee(__('admin.password_reset.reset_description'));

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = test()->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});
