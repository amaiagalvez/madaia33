<?php

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
