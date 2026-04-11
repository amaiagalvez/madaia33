<?php

use App\Models\Setting;
use App\Support\ConfiguredMailSettings;
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
        ],
        AdminSettingsValidation::rules(),
        AdminSettingsValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('adminEmail');
});

it('rejects script tags in legal texts', function () {
    $validator = Validator::make(
        [
            'adminEmail' => 'admin@example.com',
            'recaptchaSiteKey' => null,
            'recaptchaSecretKey' => null,
            'legalCheckboxTextEu' => '<script>alert(1)</script>',
            'legalCheckboxTextEs' => null,
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

it('requires a valid sender address in email configuration section rules', function () {
    $validator = Validator::make(
        [
            'emailFromAddress' => 'not-an-email',
            'smtpHost' => 'smtp.example.com',
            'smtpPort' => '587',
            'smtpEncryption' => 'tls',
            'emailLegalTextEu' => '<p>Testu legala</p>',
            'emailLegalTextEs' => '<p>Texto legal</p>',
        ],
        AdminSettingsValidation::rulesBySection(Setting::SECTION_EMAIL_CONFIGURATION),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('emailFromAddress');
});

it('accepts valid email configuration section payload', function () {
    $validator = Validator::make(
        [
            'emailFromAddress' => 'noreply@example.com',
            'emailFromName' => 'Madaia 33',
            'smtpHost' => 'smtp.example.com',
            'smtpPort' => '587',
            'smtpUsername' => 'smtp-user',
            'smtpPassword' => 'smtp-secret',
            'smtpEncryption' => 'tls',
            'emailLegalTextEu' => '<p>Testu legala</p>',
            'emailLegalTextEs' => '<p>Texto legal</p>',
        ],
        AdminSettingsValidation::rulesBySection(Setting::SECTION_EMAIL_CONFIGURATION),
    );

    expect($validator->fails())->toBeFalse();
});

it('rejects invalid smtp encryption option in email configuration section rules', function () {
    $validator = Validator::make(
        [
            'emailFromAddress' => 'noreply@example.com',
            'smtpHost' => 'smtp.example.com',
            'smtpPort' => '587',
            'smtpEncryption' => 'starttls',
        ],
        AdminSettingsValidation::rulesBySection(Setting::SECTION_EMAIL_CONFIGURATION),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('smtpEncryption')
        ->and(ConfiguredMailSettings::encryptionOptions())->toBe(['tls', 'ssl']);
});

it('accepts valid front section payload including home history text', function () {
    $validator = Validator::make(
        [
            'frontSiteName' => 'Madaia 33',
            'frontPrimaryEmail' => 'info@example.com',
            'frontLogoImagePath' => 'branding/logo.png',
            'frontPhotoRequestTextEu' => 'Bidali argazkiak',
            'frontPhotoRequestTextEs' => 'Envia fotos',
            'historyTextEu' => '<p>Historia EU</p>',
            'historyTextEs' => '<p>Historia ES</p>',
            'privacyContentEu' => '<p>Privacidad EU</p>',
            'privacyContentEs' => '<p>Privacidad ES</p>',
            'legalNoticeContentEu' => '<p>Aviso EU</p>',
            'legalNoticeContentEs' => '<p>Aviso ES</p>',
            'cookiePolicyContentEu' => '<p>Cookie EU</p>',
            'cookiePolicyContentEs' => '<p>Cookie ES</p>',
        ],
        AdminSettingsValidation::rulesBySection(Setting::SECTION_FRONT),
    );

    expect($validator->fails())->toBeFalse();
});
