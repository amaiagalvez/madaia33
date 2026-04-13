<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Notifications\Auth\ResetPasswordNotification;
use App\SupportedLocales;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Lang;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

dataset('supported_locales', SupportedLocales::all());

beforeEach(function () {
    test()->skipUnlessFortifyFeature(Features::resetPasswords());

    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

test('reset password link screen can be rendered', function () {
    $response = test()->get(route('password.request'));

    $response->assertOk()
        ->assertSee('data-auth-shell', false)
        ->assertSee(__('admin.password_reset.request_title'))
        ->assertSee(__('admin.password_reset.request_description'));
});

test('localized password request routes can be rendered', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('password.request', $locale)));

    $response->assertOk()
        ->assertSee('data-auth-shell', false)
        ->assertSee(__('admin.password_reset.request_title'));
})->with('supported_locales');

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password link can be requested with coproprietary email 1', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->assignRole(Role::PROPERTY_OWNER);

    Owner::factory()->for($user)->create([
        'coprop1_email' => 'owner1@example.com',
    ]);

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => 'owner1@example.com']);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password link can be requested with coproprietary email 2', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->assignRole(Role::PROPERTY_OWNER);

    Owner::factory()->for($user)->create([
        'coprop2_email' => 'owner2@example.com',
    ]);

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => 'owner2@example.com']);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('password reset flow applies sender configuration from settings', function () {
    createSetting('from_address', 'noreply@madaia33.test');
    createSetting('from_name', 'Madaia 33 Test');

    (new AppServiceProvider(app()))->boot();

    expect(config('mail.from.address'))->toBe('noreply@madaia33.test')
        ->and(config('mail.from.name'))->toBe('Madaia 33 Test');
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = test()->get(route('password.reset', $notification->token));

        $response->assertOk()
            ->assertSee('data-auth-shell', false)
            ->assertSee(__('admin.password_reset.reset_title'))
            ->assertSee(__('admin.password_reset.reset_description'));

        return true;
    });
});

test('password reset notification keeps the visitor locale and localized reset link', function (string $locale) {
    Notification::fake();

    $user = User::factory()->create();

    test()->get(route(SupportedLocales::routeName('password.request', $locale)));

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification, array $channels, User $notifiable, ?string $sentLocale) use ($locale, $user) {
        $expectedPath = parse_url(
            route(SupportedLocales::routeName('password.reset', $locale), [
                'token' => $notification->token,
                'email' => $user->email,
            ]),
            PHP_URL_PATH,
        );

        $originalLocale = app()->getLocale();
        app()->setLocale($sentLocale ?? $originalLocale);

        $mailMessage = $notification->toMail($user);

        app()->setLocale($originalLocale);

        expect($notifiable->is($user))->toBeTrue()
            ->and($channels)->toContain('mail')
            ->and($sentLocale)->toBe($locale)
            ->and($mailMessage->subject)->toBe(Lang::get('Reset your password', [], $locale))
            ->and($mailMessage->actionUrl)->toContain((string) $expectedPath);

        return true;
    });
})->with('supported_locales');

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
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

test('password reset notification uses the shared email template and includes legal text', function () {
    Notification::fake();

    createSetting('legal_text_eu', '<p>Ohar legal orokorra</p>');

    $user = User::factory()->create();

    test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->view)->toBe('mail.auth.reset-password')
            ->and($mailMessage->viewData['legalText'] ?? null)->toBe('<p>Ohar legal orokorra</p>');

        return true;
    });
});
