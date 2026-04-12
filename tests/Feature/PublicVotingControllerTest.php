<?php

use App\Models\User;
use App\Models\Voting;
use App\SupportedLocales;
use Illuminate\Http\Request;
use App\Http\Controllers\PublicVotingController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('returns votings public view when open votings exist', function () {
    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $view = app(PublicVotingController::class)->index();

    expect($view->name())->toBe('public.votings');
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
