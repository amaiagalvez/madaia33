<?php

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Support\VotingEligibilityService;
use App\Http\Composers\VotingsNavigationComposer;

it('sets showVotingsLink false for guests', function () {
    $eligibilityService = Mockery::mock(VotingEligibilityService::class);
    $view = Mockery::mock(View::class);

    Auth::shouldReceive('check')->once()->andReturn(false);

    $view->shouldReceive('with')
        ->once()
        ->with('showVotingsLink', false);

    (new VotingsNavigationComposer($eligibilityService))->compose($view);
});

it('falls back to showVotingsLink false when an exception occurs', function () {
    $eligibilityService = Mockery::mock(VotingEligibilityService::class);
    $view = Mockery::mock(View::class);

    Auth::shouldReceive('check')->once()->andReturn(true);

    $view->shouldReceive('with')
        ->once()
        ->with('showVotingsLink', false);

    (new VotingsNavigationComposer($eligibilityService))->compose($view);
});
