<?php

use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

test('example', function (string $locale) {
    $response = $this->get(route(SupportedLocales::routeName('home', $locale)));

    $response->assertStatus(200);
})->with('supported_locales');
