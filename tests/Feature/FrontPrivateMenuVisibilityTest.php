<?php

use App\Models\Role;
use App\Models\User;
use App\SupportedLocales;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('hides private menu link for authenticated owner-only users', function () {
    $ownerUser = User::factory()->create();
    $ownerUser->assignRole(Role::PROPERTY_OWNER);

    test()->actingAs($ownerUser)
        ->get(route(SupportedLocales::routeName('home', SupportedLocales::DEFAULT)))
        ->assertSuccessful()
        ->assertDontSee(__('general.nav.private'));
});

it('shows private menu link for guests and non owner-only users', function () {
    test()->get(route(SupportedLocales::routeName('home', SupportedLocales::DEFAULT)))
        ->assertSuccessful()
        ->assertSee(__('general.nav.private'));

    $adminUser = User::factory()->create();
    $adminUser->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($adminUser)
        ->get(route(SupportedLocales::routeName('home', SupportedLocales::DEFAULT)))
        ->assertSuccessful()
        ->assertSee(__('general.nav.private'));
});
