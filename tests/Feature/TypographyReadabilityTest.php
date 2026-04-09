<?php

use App\Models\Notice;
use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

it('notices use responsive readable body typography', function (string $locale) {
    Notice::factory()->public()->count(2)->create();

    $response = test()->get(route(SupportedLocales::routeName('notices', $locale)));

    $response->assertSuccessful();
    $response->assertSee('leading-relaxed text-gray-600 text-sm md:text-base lg:text-lg', false);
    $response->assertSee('leading-relaxed text-gray-600 text-sm md:text-base', false);
})->with('supported_locales');

it('legal pages preserve readable typography and line length', function (string $locale) {
    $privacy = test()->get(route(SupportedLocales::routeName('privacy-policy', $locale)));
    $privacy->assertSuccessful();
    $privacy->assertSee('max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $privacy->assertSee('text-base leading-relaxed', false);

    $legal = test()->get(route(SupportedLocales::routeName('legal-notice', $locale)));
    $legal->assertSuccessful();
    $legal->assertSee('max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $legal->assertSee('text-base leading-relaxed', false);
})->with('supported_locales');

it('general translations expose documented font size guidance', function () {
    app()->setLocale(SupportedLocales::BASQUE);
    expect(__('general.font_sizes.mobile'))->toBe('text-sm');
    expect(__('general.font_sizes.tablet_up'))->toBe('text-base');

    app()->setLocale(SupportedLocales::SPANISH);
    expect(__('general.font_sizes.mobile'))->toBe('text-sm');
    expect(__('general.font_sizes.tablet_up'))->toBe('text-base');
});
