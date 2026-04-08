<?php

use App\Models\Notice;
use App\SupportedLocales;

it('notices use responsive readable body typography', function () {
    Notice::factory()->public()->count(2)->create();

    $response = test()->get('/avisos');

    $response->assertSuccessful();
    $response->assertSee('leading-relaxed text-gray-600 text-sm md:text-base lg:text-lg line-clamp-4', false);
    $response->assertSee('leading-relaxed text-gray-600 text-sm md:text-base line-clamp-3', false);
});

it('legal pages preserve readable typography and line length', function () {
    $privacy = test()->get(route('privacy-policy'));
    $privacy->assertSuccessful();
    $privacy->assertSee('max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $privacy->assertSee('text-base leading-relaxed', false);

    $legal = test()->get(route('legal-notice'));
    $legal->assertSuccessful();
    $legal->assertSee('max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $legal->assertSee('text-base leading-relaxed', false);
});

it('general translations expose documented font size guidance', function () {
    app()->setLocale(SupportedLocales::BASQUE);
    expect(__('general.font_sizes.mobile'))->toBe('text-sm');
    expect(__('general.font_sizes.tablet_up'))->toBe('text-base');

    app()->setLocale(SupportedLocales::SPANISH);
    expect(__('general.font_sizes.mobile'))->toBe('text-sm');
    expect(__('general.font_sizes.tablet_up'))->toBe('text-base');
});
