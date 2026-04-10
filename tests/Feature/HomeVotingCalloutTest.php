<?php

use App\Models\Voting;
use App\SupportedLocales;

it('shows voting callout on home when an open voting exists', function () {
    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    test()->get(route(SupportedLocales::routeName('home', SupportedLocales::DEFAULT)))
        ->assertSuccessful()
        ->assertSee('data-home-votings-callout', false)
        ->assertSee(__('home.votings_cta'));
});

it('does not show voting callout on home when there are no open votings', function () {
    Voting::factory()->future()->create([
        'is_published' => true,
    ]);

    test()->get(route(SupportedLocales::routeName('home', SupportedLocales::DEFAULT)))
        ->assertSuccessful()
        ->assertDontSee('data-home-votings-callout', false);
});
