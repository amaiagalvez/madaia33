<?php

use App\Models\Owner;
use App\Models\Voting;
use App\Models\Location;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\PropertyAssignment;
use App\Support\VotingEligibilityService;

describe('VotingEligibilityService::ownerCanVote()', function () {
  it('returns true when owner has an active assignment in an allowed portal', function () {
    $portal = Location::factory()->portal()->create(['code' => '33-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);
    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property->id,
      'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true]);
    $voting->locations()->create(['location_id' => $portal->id]);

    $service = app(VotingEligibilityService::class);

    expect($service->ownerCanVote($voting, $owner))->toBeTrue();
  });

  it('returns false when owner belongs to a different portal', function () {
    $allowedPortal = Location::factory()->portal()->create(['code' => '33-A']);
    $otherPortal = Location::factory()->portal()->create(['code' => '33-B']);
    $property = Property::factory()->create(['location_id' => $otherPortal->id]);
    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property->id,
      'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true]);
    $voting->locations()->create(['location_id' => $allowedPortal->id]);

    $service = app(VotingEligibilityService::class);

    expect($service->ownerCanVote($voting, $owner))->toBeFalse();
  });

  it('returns true for any owner when voting has no location restrictions', function () {
    $portal = Location::factory()->portal()->create(['code' => '44-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);
    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property->id,
      'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true]);
    // No locations added — unrestricted

    $service = app(VotingEligibilityService::class);

    expect($service->ownerCanVote($voting, $owner))->toBeTrue();
  });

  it('returns false when owner has no active assignments', function () {
    $owner = Owner::factory()->create();
    $voting = Voting::factory()->current()->create(['is_published' => true]);

    $service = app(VotingEligibilityService::class);

    expect($service->ownerCanVote($voting, $owner))->toBeFalse();
  });
});

describe('VotingEligibilityService::percentageForOwner()', function () {
  it('returns the sum of community_pct for eligible assignments', function () {
    $portal = Location::factory()->portal()->create(['code' => '55-A']);
    $property1 = Property::factory()->create(['location_id' => $portal->id, 'community_pct' => '3.50']);
    $property2 = Property::factory()->create(['location_id' => $portal->id, 'community_pct' => '1.25']);
    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property1->id,
      'end_date' => null,
    ]);
    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property2->id,
      'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true]);
    $voting->locations()->create(['location_id' => $portal->id]);

    $service = app(VotingEligibilityService::class);
    $owner->load('activeAssignments.property.location');

    expect($service->percentageForOwner($voting, $owner))->toBe(4.75);
  });

  it('returns zero when owner has no eligible assignments', function () {
    $owner = Owner::factory()->create();
    $voting = Voting::factory()->current()->create(['is_published' => true]);

    $service = app(VotingEligibilityService::class);
    $owner->load('activeAssignments.property.location');

    expect($service->percentageForOwner($voting, $owner))->toBe(0.0);
  });
});

describe('VotingEligibilityService::ownersWithPendingDelegations()', function () {
  it('returns empty collection when no open votings exist', function () {
    $service = app(VotingEligibilityService::class);

    expect($service->ownersWithPendingDelegations())->toBeEmpty();
  });

  it('returns owner entries with pending voting count when eligible and has not voted', function () {
    $portal = Location::factory()->portal()->create(['code' => '66-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);
    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property->id,
      'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true]);
    $voting->locations()->create(['location_id' => $portal->id]);

    $service = app(VotingEligibilityService::class);
    $result = $service->ownersWithPendingDelegations();

    expect($result)->toHaveCount(1)
      ->and($result->first()['owner']->id)->toBe($owner->id)
      ->and($result->first()['pending_votings'])->toBe(1);
  });

  it('excludes owner who already voted', function () {
    $portal = Location::factory()->portal()->create(['code' => '77-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);
    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
      'owner_id' => $owner->id,
      'property_id' => $property->id,
      'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true]);
    $voting->locations()->create(['location_id' => $portal->id]);

    VotingBallot::create([
      'voting_id' => $voting->id,
      'owner_id' => $owner->id,
      'cast_by_user_id' => null,
      'voted_at' => now(),
    ]);

    $service = app(VotingEligibilityService::class);

    expect($service->ownersWithPendingDelegations())->toBeEmpty();
  });
});
