<?php

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use App\Http\Composers\VotingsNavigationComposer;

it('sets default navigation values when route does not qualify for front navigation', function () {
    request()->setRouteResolver(static fn () => null);

    $view = Mockery::mock(View::class);

    $view->shouldReceive('with')
        ->once()
        ->with('showVotingsLink', false);

    $view->shouldReceive('with')
        ->once()
        ->withArgs(static fn (string $key, mixed $value): bool => $key === 'activeConstructionsNav'
            && $value instanceof Collection
            && $value->isEmpty());

    (new VotingsNavigationComposer)->compose($view);
});

it('falls back to safe values when an exception occurs during front navigation resolution', function () {
    request()->setRouteResolver(static fn () => new Route('GET', '/', ['as' => 'home']));

    $view = Mockery::mock(View::class);

    $view->shouldReceive('with')
        ->once()
        ->with('showVotingsLink', false);

    $view->shouldReceive('with')
        ->once()
        ->withArgs(static fn (string $key, mixed $value): bool => $key === 'activeConstructionsNav'
            && $value instanceof Collection
            && $value->isEmpty());

    (new VotingsNavigationComposer)->compose($view);
});
