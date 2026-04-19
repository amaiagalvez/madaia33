<?php

use App\Models\Role;
use App\Models\Owner;
use App\Models\User;

it('shows invalid contacts owners stat in admin dashboard', function () {
    $user = adminUser();

    Owner::factory()->create([
        'coprop1_email_invalid' => true,
        'coprop1_phone_invalid' => false,
        'coprop2_email_invalid' => false,
        'coprop2_phone_invalid' => false,
    ]);

    Owner::factory()->create([
        'coprop1_email_invalid' => false,
        'coprop1_phone_invalid' => false,
        'coprop2_email_invalid' => false,
        'coprop2_phone_invalid' => true,
    ]);

    Owner::factory()->create([
        'coprop1_email_invalid' => false,
        'coprop1_phone_invalid' => false,
        'coprop2_email_invalid' => false,
        'coprop2_phone_invalid' => false,
    ]);

    test()->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('admin.stats.invalid_contacts_owners'))
        ->assertSee('2');
});

it('hides dashboard stats for non-superadmin admin users', function () {
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);

    $user = User::factory()->create();
    $user->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee(__('admin.stats.invalid_contacts_owners'))
        ->assertDontSee('data-admin-stat-invalid-contacts', false);
});
