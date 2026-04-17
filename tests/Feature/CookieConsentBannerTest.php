<?php

use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

it('renders cookie consent banner with locale-aware buttons and cookie policy link', function (string $locale) {
    $response = test()->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertOk()
        ->assertSee('data-cookie-consent-banner', false)
        ->assertSee('data-cookie-consent-understood', false)
        ->assertSee('data-cookie-policy-link', false)
        ->assertSee(route(SupportedLocales::routeName('cookie-policy', $locale)));
})->with('supported_locales');
