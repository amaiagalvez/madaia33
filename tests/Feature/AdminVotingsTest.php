<?php

use App\Livewire\Admin\Votings;
use App\Models\Location;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Models\User;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use Livewire\Livewire;

it('shows active votings with census and votes in admin component', function () {
  $admin = User::factory()->create();

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
