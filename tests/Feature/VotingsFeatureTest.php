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
use App\Livewire\PublicVotings;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PublicVotingController;

beforeEach(function () {
  foreach (Role::names() as $roleName) {
    Role::query()->firstOrCreate([
      'name' => $roleName,
    ]);
  }
});

it('requires authentication to access public votings page', function () {
  test()->get(route('votings.eu'))
    ->assertRedirect(route('login'));
});

it('allows an eligible owner to vote once and stores auditable rows', function () {
  Mail::fake();

  $owner = Owner::factory()->create();
  $portal = Location::factory()->portal()->create(['code' => '33-A']);
  $property = Property::factory()->create(['location_id' => $portal->id]);

  PropertyAssignment::factory()->create([
    'owner_id' => $owner->id,
    'property_id' => $property->id,
    'end_date' => null,
  ]);

  $voting = Voting::factory()->current()->create([
    'is_published' => true,
    'is_anonymous' => false,
  ]);

  VotingOption::factory()->create([
    'voting_id' => $voting->id,
    'position' => 1,
  ]);

  $option = VotingOption::factory()->create([
    'voting_id' => $voting->id,
    'position' => 2,
    'label_eu' => 'Bai',
    'label_es' => 'Si',
  ]);

  $voting->locations()->create(['location_id' => $portal->id]);

  Livewire::actingAs($owner->user)
    ->test(PublicVotings::class)
    ->set("selectedOptions.{$voting->id}", $option->id)
    ->call('vote', $voting->id)
    ->assertHasNoErrors();

  expect(VotingBallot::query()
    ->where('voting_id', $voting->id)
    ->where('owner_id', $owner->id)
    ->count())->toBe(1);

  expect(VotingSelection::query()
    ->where('voting_id', $voting->id)
    ->where('owner_id', $owner->id)
    ->where('voting_option_id', $option->id)
    ->count())->toBe(1);

  expect(VotingOptionTotal::query()
    ->where('voting_id', $voting->id)
    ->where('voting_option_id', $option->id)
    ->value('votes_count'))->toBe(1);

  Livewire::actingAs($owner->user)
    ->test(PublicVotings::class)
    ->set("selectedOptions.{$voting->id}", $option->id)
    ->call('vote', $voting->id)
    ->assertHasErrors(["selectedOptions.{$voting->id}"]);

  expect(VotingBallot::query()
    ->where('voting_id', $voting->id)
    ->where('owner_id', $owner->id)
    ->count())->toBe(1);
});

it('does not store option selections for anonymous votings', function () {
  Mail::fake();

  $owner = Owner::factory()->create();
  $portal = Location::factory()->portal()->create(['code' => '33-B']);
  $property = Property::factory()->create(['location_id' => $portal->id]);

  PropertyAssignment::factory()->create([
    'owner_id' => $owner->id,
    'property_id' => $property->id,
    'end_date' => null,
  ]);

  $voting = Voting::factory()->current()->anonymous()->create([
    'is_published' => true,
  ]);

  $option = VotingOption::factory()->create([
    'voting_id' => $voting->id,
    'position' => 1,
  ]);

  $voting->locations()->create(['location_id' => $portal->id]);

  Livewire::actingAs($owner->user)
    ->test(PublicVotings::class)
    ->set("selectedOptions.{$voting->id}", $option->id)
    ->call('vote', $voting->id)
    ->assertHasNoErrors();

  expect(VotingSelection::query()
    ->where('voting_id', $voting->id)
    ->count())->toBe(0);

  expect(VotingOptionTotal::query()
    ->where('voting_id', $voting->id)
    ->where('voting_option_id', $option->id)
    ->value('votes_count'))->toBe(1);
});

it('stores delegated voter user id when voting on behalf of another owner', function () {
  Mail::fake();

  $delegatedOwner = Owner::factory()->create();
  $adminUser = User::factory()->create();
  $adminUser->assignRole(Role::DELEGATED_VOTE);

  $portal = Location::factory()->portal()->create(['code' => '33-C']);
  $property = Property::factory()->create(['location_id' => $portal->id]);

  PropertyAssignment::factory()->create([
    'owner_id' => $delegatedOwner->id,
    'property_id' => $property->id,
    'end_date' => null,
  ]);

  $voting = Voting::factory()->current()->create([
    'is_published' => true,
  ]);

  $option = VotingOption::factory()->create([
    'voting_id' => $voting->id,
    'position' => 1,
  ]);

  $voting->locations()->create(['location_id' => $portal->id]);

  test()->actingAs($adminUser)
    ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => $delegatedOwner->id]);

  Livewire::actingAs($adminUser)
    ->test(PublicVotings::class)
    ->set("selectedOptions.{$voting->id}", $option->id)
    ->call('vote', $voting->id)
    ->assertHasNoErrors();

  $ballot = VotingBallot::query()
    ->where('voting_id', $voting->id)
    ->where('owner_id', $delegatedOwner->id)
    ->first();

  expect($ballot)->not->toBeNull()
    ->and($ballot->cast_by_user_id)->toBe($adminUser->id);
});

it('allows superadmin to open public votings in read only mode', function () {
  $superadmin = User::factory()->create([
    'id' => 1,
  ]);
  $superadmin->assignRole(Role::SUPER_ADMIN);

  $voting = Voting::factory()->current()->create([
    'is_published' => true,
  ]);

  $option = VotingOption::factory()->create([
    'voting_id' => $voting->id,
    'position' => 1,
  ]);

  Livewire::actingAs($superadmin)
    ->test(PublicVotings::class)
    ->assertSet('canCastVotes', false)
    ->set("selectedOptions.{$voting->id}", $option->id)
    ->call('vote', $voting->id)
    ->assertHasErrors(["selectedOptions.{$voting->id}"]);
});

it('forbids delegated voting flow for users without delegated role', function () {
  $adminUser = User::factory()->create();

  test()->actingAs($adminUser)
    ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => 999]);

  Livewire::actingAs($adminUser)
    ->test(PublicVotings::class)
    ->assertForbidden();
});
