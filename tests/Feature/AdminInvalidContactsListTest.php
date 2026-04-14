<?php

use App\Models\Owner;
use Livewire\Livewire;

it('lists invalid contacts and lets admins restore them', function () {
    $user = adminUser();

    $invalidOwner = Owner::factory()->create([
        'coprop1_name' => 'Ane',
        'coprop1_email' => 'ane@example.com',
        'coprop1_email_invalid' => true,
        'coprop1_email_error_count' => 3,
        'last_contact_error_at' => now(),
    ]);

    $validOwner = Owner::factory()->create([
        'coprop1_name' => 'Miren',
        'coprop1_email' => 'miren@example.com',
        'coprop1_email_invalid' => false,
        'coprop1_email_error_count' => 0,
    ]);

    Livewire::actingAs($user)
        ->test('admin-invalid-contacts-list')
        ->assertSee('Ane')
        ->assertSee('ane@example.com')
        ->assertDontSee('miren@example.com')
        ->call('markAsValid', $invalidOwner->id, 'coprop1', 'email');

    $invalidOwner->refresh();
    $validOwner->refresh();

    expect($invalidOwner->coprop1_email_invalid)->toBeFalse()
        ->and($invalidOwner->coprop1_email_error_count)->toBe(0)
        ->and($validOwner->coprop1_email_invalid)->toBeFalse();
});

it('renders the invalid contacts admin page', function () {
    $user = adminUser();

    test()->actingAs($user)
        ->get(route('admin.campaigns.invalid-contacts'))
        ->assertOk();
});
