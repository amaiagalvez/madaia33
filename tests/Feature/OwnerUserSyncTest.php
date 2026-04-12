<?php

use App\Models\User;
use App\Models\Owner;
use App\SupportedLocales;

test('owner updates mirror coprop1 name email and language to user', function () {
    $user = User::factory()->create([
        'name' => 'User One',
        'email' => 'user-one@example.com',
        'language' => SupportedLocales::BASQUE,
    ]);

    $owner = Owner::factory()->for($user)->create([
        'coprop1_name' => 'User One',
        'coprop1_email' => 'user-one@example.com',
        'language' => SupportedLocales::BASQUE,
    ]);

    $owner->update([
        'coprop1_name' => 'User One Updated',
        'coprop1_email' => 'user-one-updated@example.com',
        'language' => SupportedLocales::SPANISH,
    ]);

    $user->refresh();

    expect($user->name)->toEqual('User One Updated')
        ->and($user->email)->toEqual('user-one-updated@example.com')
        ->and($user->language)->toEqual(SupportedLocales::SPANISH);
});

test('user syncOwnerIdentity mirrors name email and language to owner', function () {
    $user = User::factory()->create([
        'name' => 'Second User',
        'email' => 'second-user@example.com',
        'language' => SupportedLocales::BASQUE,
    ]);

    $owner = Owner::factory()->for($user)->create([
        'coprop1_name' => 'Second User',
        'coprop1_email' => 'second-user@example.com',
        'language' => SupportedLocales::BASQUE,
    ]);

    $user->update([
        'name' => 'Second User Updated',
        'email' => 'second-user-updated@example.com',
        'language' => SupportedLocales::SPANISH,
    ]);

    $user->syncOwnerIdentity();

    $owner->refresh();

    expect($owner->coprop1_name)->toEqual('Second User Updated')
        ->and($owner->coprop1_email)->toEqual('second-user-updated@example.com')
        ->and($owner->language)->toEqual(SupportedLocales::SPANISH);
});
