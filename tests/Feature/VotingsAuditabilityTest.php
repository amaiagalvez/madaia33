<?php

use App\Models\Owner;
use App\Models\Voting;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Livewire\PublicVotings;
use App\Models\PropertyAssignment;
use App\Mail\VotingConfirmationMail;
use Illuminate\Support\Facades\Mail;
use App\Actions\Votings\CastVotingBallotAction;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\PublicVotingController;

it('sends a confirmation email after a successful vote', function () {
    Mail::fake();

    $owner = Owner::factory()->create();
    $portal = Location::factory()->portal()->create(['code' => '44-A']);
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

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $action = app(CastVotingBallotAction::class);
    $action->execute($voting, $owner, $option->id, $owner->user);

    Mail::assertSent(VotingConfirmationMail::class, function (VotingConfirmationMail $mail) use ($owner): bool {
        return $mail->hasTo($owner->user->email);
    });
});

it('rejects vote attempts when owner does not belong to the allowed locations', function () {
    Mail::fake();

    $owner = Owner::factory()->create();

    $portalAllowed = Location::factory()->portal()->create(['code' => '55-A']);
    $portalOther = Location::factory()->portal()->create(['code' => '55-B']);

    $ownerProperty = Property::factory()->create(['location_id' => $portalOther->id]);
    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $ownerProperty->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portalAllowed->id]);

    $action = app(CastVotingBallotAction::class);

    expect(fn() => $action->execute($voting, $owner, $option->id, $owner->user))
        ->toThrow(ValidationException::class);

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $owner->id)
        ->exists())->toBeFalse();

    Mail::assertNothingSent();
});

it('ignores delegated session impersonation for owner users', function () {
    Mail::fake();

    $ownerA = Owner::factory()->create();
    $ownerB = Owner::factory()->create();

    $portal = Location::factory()->portal()->create(['code' => '57-A']);
    $propertyA = Property::factory()->create(['location_id' => $portal->id]);
    $propertyB = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerA->id,
        'property_id' => $propertyA->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerB->id,
        'property_id' => $propertyB->id,
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

    test()->actingAs($ownerA->user)
        ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => $ownerB->id]);

    Livewire::actingAs($ownerA->user)
        ->test(PublicVotings::class)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $ownerA->id)
        ->exists())->toBeTrue();

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $ownerB->id)
        ->exists())->toBeFalse();
});
