<?php

// Feature: community-web, Tarea 7: Formulario de contacto
// Valida: Requisitos 10.1–10.6, 11.1–11.5, 12.1–12.3, 13.1–13.3

use Livewire\Livewire;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['app.recaptcha_skip' => true]);
    createSetting('admin_email', 'admin@example.com');
});

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 10: Limpieza de campos tras envío exitoso
// Valida: Requisito 10.6
// ─────────────────────────────────────────────────────────────────────────────

it('limpia todos los campos tras un envío exitoso', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Proba')
        ->set('message', 'Kaixo!')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('subject', '')
        ->assertSet('message', '')
        ->assertSet('legalAccepted', false)
        ->assertSet('recaptchaToken', '');
});

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 11: Emails despachados con contenido correcto
// Valida: Requisitos 11.1, 11.2, 11.3, 11.4
// ─────────────────────────────────────────────────────────────────────────────

it('despacha ContactConfirmation y ContactNotification', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Proba gaia')
        ->set('message', 'Kaixo, mezu bat bidaltzen dut.')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    Mail::assertSent(ContactConfirmation::class);
    Mail::assertSent(ContactNotification::class);
});

// ─────────────────────────────────────────────────────────────────────────────
// Propiedad 12: Rechazo con score de reCAPTCHA bajo
// Valida: Requisito 12.2
// ─────────────────────────────────────────────────────────────────────────────

it('rechaza el envío cuando el score de reCAPTCHA es inferior al umbral', function (float $score) {
    config(['app.recaptcha_skip' => false]);
    createSetting('recaptcha_secret_key', 'test-secret');

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => $score,
        ]),
    ]);

    $component = Livewire::test('contact-form')
        ->set('name', 'Ane')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'low-score-token')
        ->call('submit');

    $component->assertSet('statusType', 'error');
    expect(ContactMessage::count())->toBe(0);
})->with([0.0, 0.49]);

// ─────────────────────────────────────────────────────────────────────────────
// Tests de ejemplo
// Valida: Requisitos 10.5, 11.5, 12.3
// ─────────────────────────────────────────────────────────────────────────────

it('happy path: guarda ContactMessage y despacha ambos emails', function () {
    Mail::fake();

    $component = Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Proba')
        ->set('message', 'Kaixo!')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    expect(ContactMessage::count())->toBe(1);
    Mail::assertSent(ContactConfirmation::class);
    Mail::assertSent(ContactNotification::class);
    $component->assertSet('statusType', 'success');
});

it('fallo de email: guarda el mensaje y muestra advertencia', function () {
    Log::spy();

    Mail::shouldReceive('to')->andThrow(new Exception('SMTP error'));

    $component = Livewire::test('contact-form')
        ->set('name', 'Ane')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    expect(ContactMessage::count())->toBe(1);
    $component->assertSet('statusType', 'warning');
});

it('fallo de reCAPTCHA por error externo: rechaza el envío', function () {
    config(['app.recaptcha_skip' => false]);
    createSetting('recaptcha_secret_key', 'test-secret');

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
    ]);

    $component = Livewire::test('contact-form')
        ->set('name', 'Ane')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'bad-token')
        ->call('submit');

    $component->assertSet('statusType', 'error');
    expect(ContactMessage::count())->toBe(0);
});
