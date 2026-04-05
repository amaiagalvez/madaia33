<?php

use Illuminate\Support\Facades\Validator;
use App\Validations\ContactFormValidation;

it('rejects empty required fields', function (string $emptyField) {
    $data = [
        'name' => 'Ane Etxebarria',
        'email' => 'ane@example.com',
        'subject' => 'Proba',
        'message' => 'Kaixo!',
        'legalAccepted' => true,
        'recaptchaToken' => 'token',
    ];

    $data[$emptyField] = '';

    $validator = Validator::make(
        $data,
        ContactFormValidation::rules(''),
        ContactFormValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain($emptyField);
})->with(['name', 'email', 'subject', 'message']);

it('rejects invalid email formats', function (string $invalidEmail) {
    $data = [
        'name' => 'Ane',
        'email' => $invalidEmail,
        'subject' => 'Gaia',
        'message' => 'Mezua',
        'legalAccepted' => true,
        'recaptchaToken' => 'token',
    ];

    $validator = Validator::make(
        $data,
        ContactFormValidation::rules(''),
        ContactFormValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('email');
})->with(['no-es-email', 'falta@', '@dominio.com']);

it('requires recaptcha token when site key is configured', function () {
    $data = [
        'name' => 'Ane',
        'email' => 'ane@example.com',
        'subject' => 'Gaia',
        'message' => 'Mezua',
        'legalAccepted' => true,
        'recaptchaToken' => '',
    ];

    $validator = Validator::make(
        $data,
        ContactFormValidation::rules('site-key-configured'),
        ContactFormValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('recaptchaToken');
});

it('does not require recaptcha token when site key is empty', function () {
    $data = [
        'name' => 'Ane',
        'email' => 'ane@example.com',
        'subject' => 'Gaia',
        'message' => 'Mezua',
        'legalAccepted' => true,
        'recaptchaToken' => '',
    ];

    $validator = Validator::make(
        $data,
        ContactFormValidation::rules(''),
        ContactFormValidation::messages(),
    );

    expect($validator->fails())->toBeFalse();
});

it('requires legal acceptance', function (mixed $legalAccepted) {
    $data = [
        'name' => 'Ane',
        'email' => 'ane@example.com',
        'subject' => 'Gaia',
        'message' => 'Mezua',
        'legalAccepted' => $legalAccepted,
        'recaptchaToken' => 'token',
    ];

    $validator = Validator::make(
        $data,
        ContactFormValidation::rules(''),
        ContactFormValidation::messages(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('legalAccepted');
})->with([false, null, '']);
