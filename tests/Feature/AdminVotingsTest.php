<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Livewire\Admin\Votings;
use App\Models\PropertyAssignment;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('allows superadmin id 1 to access admin votings route', function () {
    $superadmin = User::factory()->create([
        'id' => 1,
        'email' => 'superadmin@example.com',
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    test()->actingAs($superadmin)
        ->get(route('admin.votings'))
        ->assertOk();
});

it('shows active votings with census and votes in admin component', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create();
    $portal = Location::factory()->portal()->create(['code' => '88-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Aurrekontua 2026',
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => null,
        'voted_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->assertSee('Aurrekontua 2026')
        ->assertSee('1');
});

it('shows delegated voter name in voters modal for audit visibility', function () {
    $admin = User::factory()->create(['name' => 'Admin Delegatua']);
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create(['coprop1_name' => 'Jabe Delegatua']);
    $portal = Location::factory()->portal()->create(['code' => '99-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $admin->id,
        'voted_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openVoters', $voting->id)
        ->assertSet('showOwnersModal', true)
        ->assertSee('Jabe Delegatua')
        ->assertSee('Admin Delegatua');
});

it('forbids admin voting manager access for owner accounts', function () {
    $ownerUser = User::factory()->create();
    Owner::factory()->for($ownerUser)->create();

    Livewire::actingAs($ownerUser)
        ->test(Votings::class)
        ->assertForbidden();
});

it('filters delegated vote modal by owner and location search terms', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $ownerPortal = Owner::factory()->create([
        'coprop1_name' => 'Ane Koop1',
        'coprop2_name' => 'Ane Koop2',
    ]);

    $ownerGarage = Owner::factory()->create([
        'coprop1_name' => 'Bea Koop1',
        'coprop2_name' => 'Bea Koop2',
    ]);

    $portal = Location::factory()->portal()->create(['code' => 'P-301']);
    $garage = Location::factory()->garage()->create(['code' => 'G-88']);

    $portalProperty = Property::factory()->create(['location_id' => $portal->id]);
    $garageProperty = Property::factory()->create(['location_id' => $garage->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerPortal->id,
        'property_id' => $portalProperty->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerGarage->id,
        'property_id' => $garageProperty->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);
    $voting->locations()->create(['location_id' => $garage->id]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openDelegatedVoteModal')
        ->assertSee('Ane Koop1')
        ->assertSee('Bea Koop1')
        ->set('delegatedSearch', 'P-301')
        ->assertSee('Ane Koop1')
        ->assertDontSee('Bea Koop1')
        ->set('delegatedSearch', 'Bea Koop2')
        ->assertSee('Bea Koop1')
        ->assertDontSee('Ane Koop1');
});

it('starts delegated vote and redirects to public votings route', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Delegazio Jabea',
    ]);

    $portal = Location::factory()->portal()->create(['code' => 'P-777']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($admin)
        ->withQueryParams([])
        ->test(Votings::class)
        ->call('openDelegatedVoteModal')
        ->call('startDelegatedVote', $owner->id)
        ->assertRedirect(route('votings.eu'));

    expect(session('delegated_voting_owner_id'))->toBe($owner->id);
});
