<?php

// Feature: community-web, Tarea 7: Formulario de contacto
// Valida: Requisitos 10.1–10.6, 11.1–11.5, 12.1–12.3, 13.1–13.3

use App\Models\User;
use Livewire\Livewire;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

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
})->with([0.49]);

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

it('evita envíos duplicados cuando se repite rápidamente el mismo payload', function () {
    Mail::fake();

    $payload = [
        'name' => 'Ane Etxebarria',
        'email' => 'ane@example.com',
        'subject' => 'Proba',
        'message' => 'Kaixo!',
        'legalAccepted' => true,
        'recaptchaToken' => 'skip',
    ];

    Livewire::test('contact-form')
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('subject', $payload['subject'])
        ->set('message', $payload['message'])
        ->set('legalAccepted', $payload['legalAccepted'])
        ->set('recaptchaToken', $payload['recaptchaToken'])
        ->call('submit')
        ->assertSet('statusType', 'success');

    Livewire::test('contact-form')
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('subject', $payload['subject'])
        ->set('message', $payload['message'])
        ->set('legalAccepted', $payload['legalAccepted'])
        ->set('recaptchaToken', $payload['recaptchaToken'])
        ->call('submit')
        ->assertSet('statusType', 'success');

    expect(ContactMessage::count())->toBe(1);
    Mail::assertSent(ContactConfirmation::class, 1);
    Mail::assertSent(ContactNotification::class, 1);
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

it('rechaza payload xss con script tags y no guarda el mensaje', function () {
    Mail::fake();

    $payload = '<script>alert("xss")</script>';

    Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Consulta')
        ->set('message', $payload)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertHasErrors(['message']);

    expect(ContactMessage::count())->toBe(0);
});

it('trata payload sql-like como texto plano en la bandeja admin', function () {
    Mail::fake();

    $payload = "' OR 1=1 --";

    Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Consulta SQL')
        ->set('message', $payload)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    $message = ContactMessage::query()->firstOrFail();

    expect($message->message)->toBe($payload);

    Livewire::actingAs(User::factory()->create())
        ->test('admin-message-inbox')
        ->call('openMessage', $message->id)
        ->assertSeeText($payload);
});

it('si reCAPTCHA lanza excepción rechaza el envío y no guarda mensaje', function () {
    config(['app.recaptcha_skip' => false]);
    createSetting('recaptcha_secret_key', 'test-secret');

    Http::fake(function () {
        throw new RuntimeException('Recaptcha service unavailable');
    });

    Livewire::test('contact-form')
        ->set('name', 'Ane')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'bad-token')
        ->call('submit')
        ->assertSet('statusType', 'error');

    expect(ContactMessage::count())->toBe(0);
});

it('render usa fallback de legal text y privacy policy cuando faltan settings', function () {
    Route::get('/privacy-policy', fn () => 'ok')->name('privacy-policy');

    Livewire::test('contact-form')
        ->assertViewHas('legalText', __('contact.legal_text'))
        ->assertViewHas('legalUrl', route('privacy-policy'))
        ->assertViewHas('siteKey', '');
});

it('ignora recent submissions inválidas en sesión y permite el envío', function () {
    Mail::fake();

    session(['contact_form_recent_submissions' => 'invalid']);

    Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Proba')
        ->set('message', 'Kaixo!')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertSet('statusType', 'success');

    expect(ContactMessage::count())->toBe(1);
});

it('si no hay recaptcha secret key acepta el envío aunque recaptcha_skip sea false', function () {
    Mail::fake();
    config(['app.recaptcha_skip' => false]);

    Livewire::test('contact-form')
        ->set('name', 'Ane Etxebarria')
        ->set('email', 'ane@example.com')
        ->set('subject', 'Sin secret')
        ->set('message', 'Kaixo!')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'token-cualquiera')
        ->call('submit')
        ->assertSet('statusType', 'success');

    expect(ContactMessage::count())->toBe(1);
});

it('render usa el primer legal text disponible de la cadena de fallback', function () {
    createSetting('legal_checkbox_text_es', 'Texto legal ES desde settings');

    Livewire::test('contact-form')
        ->assertViewHas('legalText', 'Texto legal ES desde settings');
});
