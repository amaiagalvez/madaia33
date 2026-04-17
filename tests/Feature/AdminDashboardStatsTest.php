<?php

use App\Models\Owner;

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
