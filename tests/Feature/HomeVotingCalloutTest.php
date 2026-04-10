<?php

use App\Models\Voting;
use App\SupportedLocales;

dataset('home_voting_locales', SupportedLocales::all());

it('shows voting callout on home when an open voting exists', function (string $locale) {
  Voting::factory()->current()->create([
    'is_published' => true,
  ]);

  test()->get(route(SupportedLocales::routeName('home', $locale)))
    ->assertSuccessful()
    ->assertSee('data-home-votings-callout', false)
    ->assertSee(__('home.votings_cta'));
})->with('home_voting_locales');

it('does not show voting callout on home when there are no open votings', function (string $locale) {
  Voting::factory()->future()->create([
    'is_published' => true,
  ]);

  test()->get(route(SupportedLocales::routeName('home', $locale)))
    ->assertSuccessful()
    ->assertDontSee('data-home-votings-callout', false);
})->with('home_voting_locales');
