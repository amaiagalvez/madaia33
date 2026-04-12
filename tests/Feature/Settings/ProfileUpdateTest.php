<?php

use App\Models\User;
use App\Models\Owner;
use Livewire\Livewire;
use App\SupportedLocales;

test('profile page is displayed', function () {
    test()->actingAs($user = User::factory()->create());

    test()->get(route('profile.edit'))->assertOk();
});

test('profile alias redirects to the localized profile page', function () {
    test()->actingAs(User::factory()->create());

    test()->get(route('profile'))->assertRedirect(route('profile.eu', absolute: false));
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    test()->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('language', SupportedLocales::SPANISH)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->language)->toEqual(SupportedLocales::SPANISH);
    expect($user->email_verified_at)->toBeNull();
});

test('profile update mirrors name email and language to owner', function () {
    $user = User::factory()->create([
        'name' => 'Initial Name',
        'email' => 'initial@example.com',
        'language' => SupportedLocales::BASQUE,
    ]);

    Owner::factory()->for($user)->create([
        'coprop1_name' => 'Initial Name',
        'coprop1_email' => 'initial@example.com',
        'language' => SupportedLocales::BASQUE,
    ]);

    test()->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->set('language', SupportedLocales::SPANISH)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $owner = $user->owner()->firstOrFail();

    expect($owner->coprop1_name)->toEqual('Updated Name')
        ->and($owner->coprop1_email)->toEqual('updated@example.com')
        ->and($owner->language)->toEqual(SupportedLocales::SPANISH);
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    test()->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    test()->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->not->toBeNull();
    expect($user->fresh()->deleted_at)->not->toBeNull();
    expect(auth()->guard()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    test()->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
