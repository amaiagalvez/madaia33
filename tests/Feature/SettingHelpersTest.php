<?php

use App\Models\Setting;
use App\SupportedLocales;

it('returns fallback for missing scalar setting', function () {
    expect(Setting::stringValue('missing_key', 'fallback-value'))->toBe('fallback-value');
});

it('returns scalar setting values as strings keyed by setting key', function () {
    createSetting('sample_key', 123);
    createSetting('second_key', 'abc');

    expect(Setting::stringValues(['sample_key', 'second_key']))->toBe([
        'sample_key' => '123',
        'second_key' => 'abc',
    ]);
});

it('resolves localized setting using locale fallback chain', function () {
    createSetting('legal_page_privacy_policy_eu', 'EU content');
    createSetting('legal_page_privacy_policy_es', 'ES content');

    expect(Setting::localizedString('legal_page_privacy_policy', '', SupportedLocales::SPANISH))
        ->toBe('ES content')
        ->and(Setting::localizedString('legal_page_privacy_policy', '', SupportedLocales::BASQUE))
        ->toBe('EU content');
});

it('resolves localized value from preloaded settings map', function () {
    $settings = [
        'legal_checkbox_text_es' => '   ',
        'legal_checkbox_text_eu' => '<p>EU fallback</p>',
    ];

    expect(Setting::localizedStringFrom($settings, 'legal_checkbox_text', 'default', SupportedLocales::SPANISH))
        ->toBe('<p>EU fallback</p>');
});
