<?php

use Illuminate\Contracts\View\View;
use App\Http\Composers\VotingsNavigationComposer;

it('sets showVotingsLink in the view context', function () {
    $view = Mockery::mock(View::class);

    $view->shouldReceive('with')
        ->once()
        ->withArgs(static fn(string $key, bool $value): bool => $key === 'showVotingsLink');

    (new VotingsNavigationComposer)->compose($view);
});

it('falls back to showVotingsLink false when an exception occurs', function () {
    $view = Mockery::mock(View::class);

    $view->shouldReceive('with')
        ->once()
        ->with('showVotingsLink', false);

    (new VotingsNavigationComposer)->compose($view);
});
