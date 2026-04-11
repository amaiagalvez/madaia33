<?php

use App\Models\Setting;
use App\SupportedLocales;
use Illuminate\Support\Facades\DB;

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

it('caches settings lookups across repeated reads', function () {
    createSetting('cached_key', 'cached-value');

    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(Setting::stringValue('cached_key'))->toBe('cached-value')
        ->and(Setting::stringValue('cached_key'))->toBe('cached-value');

    $settingsQueries = collect(DB::getQueryLog())
        ->filter(static function (array $query): bool {
            $sql = strtolower((string) ($query['query'] ?? ''));

            return str_contains($sql, 'from "settings"') || str_contains($sql, 'from `settings`');
        });

    expect($settingsQueries)->toHaveCount(1);
});

it('invalidates the cached map after a setting update', function () {
    $setting = createSetting('cached_key', 'first-value');

    expect(Setting::stringValue('cached_key'))->toBe('first-value');

    $setting->update(['value' => 'second-value']);

    expect(Setting::stringValue('cached_key'))->toBe('second-value');
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
