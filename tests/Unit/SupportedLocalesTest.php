<?php

use App\SupportedLocales;

it('normalizes invalid locales to the configured default', function () {
    expect(SupportedLocales::normalize('fr'))->toBe(SupportedLocales::default())
        ->and(SupportedLocales::normalize(null))->toBe(SupportedLocales::default());
});

it('builds fallback chains starting from the requested locale', function () {
    expect(SupportedLocales::fallbackChain(SupportedLocales::SPANISH))->toBe([
        SupportedLocales::SPANISH,
        SupportedLocales::BASQUE,
    ]);
});

it('builds localized setting keys for the fallback chain', function () {
    expect(SupportedLocales::localizedKeys('legal_checkbox_text', SupportedLocales::SPANISH))->toBe([
        'legal_checkbox_text_'.SupportedLocales::SPANISH,
        'legal_checkbox_text_'.SupportedLocales::BASQUE,
    ]);
});

it('returns locale metadata values with fallback for unsupported locales', function () {
    expect(SupportedLocales::adminTabTranslationKey(SupportedLocales::BASQUE))->toBe('EUS')
        ->and(SupportedLocales::propertySuffix(SupportedLocales::SPANISH))->toBe('Es')
        ->and(SupportedLocales::switcherLabel('fr'))->toBe('EU');
});
