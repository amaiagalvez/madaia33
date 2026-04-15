<?php

use App\Support\ConfiguredMailSettings;

const CONFIGURED_MAIL_FROM_ADDRESS = 'noreply@example.com';
const CONFIGURED_MAIL_FROM_NAME = 'Madaia 33';

it('encrypts smtp password for storage and decrypts it for display', function () {
    $service = app(ConfiguredMailSettings::class);

    $storedValue = $service->storeValue('smtp_password', 'super-secret');

    expect($storedValue)->not->toBe('super-secret')
        ->and($service->displayValue('smtp_password', $storedValue))->toBe('super-secret');
});

it('builds runtime smtp config from stored settings', function () {
    config()->set('mail.mailers.smtp.host', 'smtp-initial.local');

    $service = app(ConfiguredMailSettings::class);

    $runtimeConfig = $service->runtimeConfig([
        'from_address' => CONFIGURED_MAIL_FROM_ADDRESS,
        'from_name' => CONFIGURED_MAIL_FROM_NAME,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '2525',
        'smtp_username' => 'smtp-user',
        'smtp_password' => $service->storeValue('smtp_password', 'smtp-secret'),
        'smtp_encryption' => 'tls',
    ]);

    expect($runtimeConfig)
        ->toMatchArray([
            'mail.default' => 'smtp',
            'mail.from.address' => CONFIGURED_MAIL_FROM_ADDRESS,
            'mail.from.name' => CONFIGURED_MAIL_FROM_NAME,
            'mail.mailers.smtp.host' => 'smtp.example.com',
            'mail.mailers.smtp.port' => 2525,
            'mail.mailers.smtp.username' => 'smtp-user',
            'mail.mailers.smtp.password' => 'smtp-secret',
            'mail.mailers.smtp.scheme' => 'tls',
        ]);
});

it('only overrides from data when smtp host is missing', function () {
    $service = app(ConfiguredMailSettings::class);

    $runtimeConfig = $service->runtimeConfig([
        'from_address' => CONFIGURED_MAIL_FROM_ADDRESS,
        'from_name' => CONFIGURED_MAIL_FROM_NAME,
        'smtp_host' => '',
    ]);

    expect($runtimeConfig)->toBe([
        'mail.from.address' => CONFIGURED_MAIL_FROM_ADDRESS,
        'mail.from.name' => CONFIGURED_MAIL_FROM_NAME,
    ]);
});

it('applies stored smtp settings even if the current host is mailhog', function () {
    config()->set('mail.mailers.smtp.host', 'mailhog');

    $service = app(ConfiguredMailSettings::class);

    $runtimeConfig = $service->runtimeConfig([
        'from_address' => CONFIGURED_MAIL_FROM_ADDRESS,
        'from_name' => CONFIGURED_MAIL_FROM_NAME,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_username' => 'smtp-user',
        'smtp_password' => $service->storeValue('smtp_password', 'smtp-secret'),
        'smtp_encryption' => 'tls',
    ]);

    expect($runtimeConfig)
        ->toMatchArray([
            'mail.default' => 'smtp',
            'mail.from.address' => CONFIGURED_MAIL_FROM_ADDRESS,
            'mail.from.name' => CONFIGURED_MAIL_FROM_NAME,
            'mail.mailers.smtp.host' => 'smtp.example.com',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.username' => 'smtp-user',
            'mail.mailers.smtp.password' => 'smtp-secret',
            'mail.mailers.smtp.scheme' => 'tls',
        ]);
});
