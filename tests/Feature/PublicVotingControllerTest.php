<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Location;
use App\Models\Property;
use App\SupportedLocales;
use App\Models\VotingOption;
use Illuminate\Http\Request;
use App\Models\PropertyAssignment;
use App\Http\Controllers\PublicVotingController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('returns votings public view when open votings exist', function () {
    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
    ]);

    $owner = Owner::factory()->create();
    $location = Location::factory()->portal()->create();
    $property = Property::factory()->create(['location_id' => $location->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    test()->actingAs($owner->user);

    $response = app(PublicVotingController::class)->index();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getOriginalContent()->name())->toBe('public.votings');
});

it('aborts with 404 when there are no open votings and no pending delegations', function () {
    app(PublicVotingController::class)->index();
})->throws(NotFoundHttpException::class);

it('clears delegated and in-person voting session keys', function () {
    User::factory()->create();

    $session = app('session')->driver();
    $session->start();
    $session->put(PublicVotingController::DELEGATED_OWNER_SESSION_KEY, 123);
    $session->put(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY, 456);

    $request = Request::create('/votings/delegated/clear', 'POST');
    $request->setLaravelSession($session);

    $response = app(PublicVotingController::class)->clearDelegatedVoting($request);

    expect($session->has(PublicVotingController::DELEGATED_OWNER_SESSION_KEY))->toBeFalse()
        ->and($session->has(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY))->toBeFalse()
        ->and($response->getTargetUrl())->toBe(route(SupportedLocales::routeName('votings')));
});
