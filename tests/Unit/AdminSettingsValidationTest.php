<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use App\Validations\AdminSettingsValidation;

it('rejects empty admin email', function () {
    $validator = Validator::make(
        [
            'adminEmail' => '',
            'recaptchaSiteKey' => null,
            'recaptchaSecretKey' => null,
            'legalCheckboxTextEu' => null,
            'legalCheckboxTextEs' => null,
            'legalUrl' => null,
        ],
        AdminSettingsValidation::rules(),
        AdminSettingsValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('adminEmail');
});

it('rejects invalid admin email format', function () {
    $validator = Validator::make(
        [
            'adminEmail' => 'not-an-email',
            'recaptchaSiteKey' => null,
            'recaptchaSecretKey' => null,
            'legalCheckboxTextEu' => null,
            'legalCheckboxTextEs' => null,
            'legalUrl' => null,
        ],
        AdminSettingsValidation::rules(),
        AdminSettingsValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('adminEmail');
});

it('rejects invalid legal url format', function () {
    $validator = Validator::make(
        [
            'adminEmail' => 'admin@example.com',
            'recaptchaSiteKey' => null,
            'recaptchaSecretKey' => null,
            'legalCheckboxTextEu' => null,
            'legalCheckboxTextEs' => null,
            'legalUrl' => 'invalid-url',
        ],
        AdminSettingsValidation::rules(),
        AdminSettingsValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('legalUrl');
});

it('rejects script tags in legal texts', function () {
    $validator = Validator::make(
        [
            'adminEmail' => 'admin@example.com',
            'recaptchaSiteKey' => null,
            'recaptchaSecretKey' => null,
            'legalCheckboxTextEu' => '<script>alert(1)</script>',
            'legalCheckboxTextEs' => null,
            'legalUrl' => null,
        ],
        AdminSettingsValidation::rules(),
        AdminSettingsValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('legalCheckboxTextEu');
});

it('accepts valid admin settings payload', function () {
    $validator = Validator::make(
        [
            'adminEmail' => 'admin@example.com',
            'recaptchaSiteKey' => 'site-key-123',
            'recaptchaSecretKey' => 'secret-key-456',
            'legalCheckboxTextEu' => 'Pribatutasun-politika onartzen dut',
            'legalCheckboxTextEs' => 'Acepto la politica de privacidad',
            'legalUrl' => 'https://example.com/privacidad',
        ],
        AdminSettingsValidation::rules(),
        AdminSettingsValidation::messages(),
    );

    expect($validator->fails())->toBeFalse();
});

// ─────────────────────────────────────────────────────────────────────────────
// T022 — Section allow-list validation
// ─────────────────────────────────────────────────────────────────────────────

it('rejects invalid section value', function () {
    $validator = Validator::make(
        ['section' => 'invalid_section'],
        AdminSettingsValidation::sectionRules(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('section');
});

it('rejects missing section value', function () {
    $validator = Validator::make(
        [],
        AdminSettingsValidation::sectionRules(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('section');
});

it('accepts all defined allowed section values', function () {
    foreach (Setting::allowedSections() as $section) {
        $validator = Validator::make(
            ['section' => $section],
            AdminSettingsValidation::sectionRules(),
        );

        expect($validator->fails())->toBeFalse();
    }
});

it('requires admin email in contact form section rules', function () {
    $validator = Validator::make(
        [
            'adminEmail' => '',
            'legalCheckboxTextEu' => null,
            'legalCheckboxTextEs' => null,
            'legalUrl' => null,
        ],
        AdminSettingsValidation::rulesBySection(Setting::SECTION_CONTACT_FORM),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('adminEmail');
});

it('accepts valid recaptcha section payload', function () {
    $validator = Validator::make(
        [
            'recaptchaSiteKey' => 'site-key-123',
            'recaptchaSecretKey' => 'secret-key-456',
        ],
        AdminSettingsValidation::rulesBySection(Setting::SECTION_RECAPTCHA),
    );

    expect($validator->fails())->toBeFalse();
});
