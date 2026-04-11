<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\UserLoginSession;
use App\Models\PropertyAssignment;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('shows profile tabs with user voting and session information', function () {
    $user = User::factory()->create([
        'name' => 'Profile User',
    ]);

    $owner = Owner::factory()->for($user)->create([
        'coprop1_name' => 'Profile User',
        'coprop1_email' => 'profile@example.com',
    ]);

    $property = Property::factory()->create();

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
        'owner_validated' => false,
    ]);

    $voting = Voting::factory()->create();

    VotingBallot::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $user->id,
        'voted_at' => now()->subHour(),
    ]);

    UserLoginSession::factory()->closed()->create([
        'user_id' => $user->id,
        'logged_in_at' => now()->subHours(3),
        'logged_out_at' => now()->subHours(2),
    ]);

    test()->actingAs($user)
        ->get(route('profile.eu'))
        ->assertOk()
        ->assertSee(__('profile.tabs.votings'))
        ->assertSee(__('profile.tabs.sessions'))
        ->assertSee(__('profile.tabs.owner'))
        ->assertSee('Profile User');
});

it('accepts owner terms from profile page', function () {
    $user = User::factory()->create([
        'accepted_terms_at' => null,
    ]);

    Owner::factory()->for($user)->create();

    createSetting('owners_terms_text_eu', '<p>Baldintzak testean</p>');

    test()->actingAs($user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('profile.terms.accept.eu'))
        ->assertRedirect(route('profile.eu', ['tab' => 'owner'], false));

    expect($user->refresh()->accepted_terms_at)->not->toBeNull();
});

it('validates only authenticated owner assignments', function () {
    $user = User::factory()->create();
    $owner = Owner::factory()->for($user)->create();

    $otherUser = User::factory()->create();
    $otherOwner = Owner::factory()->for($otherUser)->create();

    $ownedAssignment = PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'end_date' => null,
        'owner_validated' => false,
    ]);

    $foreignAssignment = PropertyAssignment::factory()->create([
        'owner_id' => $otherOwner->id,
        'end_date' => null,
        'owner_validated' => false,
    ]);

    test()->actingAs($user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('profile.properties.validate.eu'), [
            'assignment_ids' => [$ownedAssignment->id, $foreignAssignment->id],
        ])
        ->assertRedirect(route('profile.eu', ['tab' => 'owner'], false));

    expect($ownedAssignment->refresh()->owner_validated)->toBeTrue()
        ->and($foreignAssignment->refresh()->owner_validated)->toBeFalse();
});
