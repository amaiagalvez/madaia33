<?php

use App\SupportedLocales;

it('general translations expose documented font size guidance', function () {
    app()->setLocale(SupportedLocales::BASQUE);
    expect(__('general.font_sizes.mobile'))->toBe('text-sm');
    expect(__('general.font_sizes.tablet_up'))->toBe('text-base');

    app()->setLocale(SupportedLocales::SPANISH);
    expect(__('general.font_sizes.mobile'))->toBe('text-sm');
    expect(__('general.font_sizes.tablet_up'))->toBe('text-base');
});
