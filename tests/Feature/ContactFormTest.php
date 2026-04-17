<?php

// Feature: community-web, Task 7: Contact form
// Validates: Requirements 10.1–10.6, 11.1–11.5, 12.1–12.3, 13.1–13.3

use App\Models\User;
use App\Models\Owner;
use Livewire\Livewire;
use App\SupportedLocales;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use App\Models\CampaignRecipient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;

const CONTACT_FORM_VISITOR_NAME = 'Ane Etxebarria';
const CONTACT_FORM_VISITOR_EMAIL = 'ane@example.com';
const CONTACT_FORM_SHORT_MESSAGE = 'Kaixo!';
const CONTACT_FORM_FROM_ADDRESS = 'mailhog@example.test';
const CONTACT_FORM_FROM_NAME = 'Madaia 33';
const CONTACT_FORM_LEGAL_TEXT_EU = '<p>Ohar legalaren testua</p>';

dataset('supported_locales', SupportedLocales::all());

beforeEach(function () {
    config(['app.recaptcha_skip' => true]);
    createSetting('admin_email', 'admin@example.com');
    createSetting('from_address', CONTACT_FORM_FROM_ADDRESS);
    createSetting('from_name', CONTACT_FORM_FROM_NAME);
    createSetting('smtp_host', 'smtp.example.test');
    createSetting('smtp_port', '2525');
    createSetting('smtp_username', 'smtp-user');
    createSetting('smtp_password', app(ConfiguredMailSettings::class)->storeValue('smtp_password', 'smtp-secret'));
    createSetting('smtp_encryption', 'tls');
    createSetting('legal_text_eu', CONTACT_FORM_LEGAL_TEXT_EU);
    createSetting('contact_form_subject_eu', 'Gaia settings EU');
    createSetting('contact_form_subject_es', 'Asunto settings ES');
});

// ─────────────────────────────────────────────────────────────────────────────
// Property 10: Field reset after successful submission
// Validates: Requirement 10.6
// ─────────────────────────────────────────────────────────────────────────────

it('clears all fields after a successful submission', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Proba')
        ->set('message', CONTACT_FORM_SHORT_MESSAGE)
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
// Property 11: Dispatched emails contain correct content
// Validates: Requirements 11.1, 11.2, 11.3, 11.4
// ─────────────────────────────────────────────────────────────────────────────

it('dispatches ContactConfirmation and ContactNotification', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Proba gaia')
        ->set('message', 'Kaixo, mezu bat bidaltzen dut.')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    Mail::assertSent(ContactConfirmation::class, function (ContactConfirmation $mail): bool {
        return $mail->messageSubject === 'Gaia settings EU'
            && $mail->fromAddress === CONTACT_FORM_FROM_ADDRESS
            && $mail->fromName === CONTACT_FORM_FROM_NAME
            && $mail->legalText === CONTACT_FORM_LEGAL_TEXT_EU;
    });

    Mail::assertSent(ContactNotification::class);
});

it('applies SMTP configuration stored in settings before sending', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'SMTP test')
        ->set('message', CONTACT_FORM_SHORT_MESSAGE)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.from.address'))->toBe(CONTACT_FORM_FROM_ADDRESS)
        ->and(config('mail.from.name'))->toBe(CONTACT_FORM_FROM_NAME)
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.test')
        ->and(config('mail.mailers.smtp.port'))->toBe(2525)
        ->and(config('mail.mailers.smtp.username'))->toBe('smtp-user')
        ->and(config('mail.mailers.smtp.password'))->toBe('smtp-secret')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('tls');
});

// ─────────────────────────────────────────────────────────────────────────────
// Property 12: Rejects low reCAPTCHA score
// Validates: Requirement 12.2
// ─────────────────────────────────────────────────────────────────────────────

it('rejects submission when reCAPTCHA score is below threshold', function (float $score) {
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
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'low-score-token')
        ->call('submit');

    $component->assertSet('statusType', 'error');
    expect(ContactMessage::count())->toBe(0);
})->with([0.49]);

// ─────────────────────────────────────────────────────────────────────────────
// Example tests
// Validates: Requirements 10.5, 11.5, 12.3
// ─────────────────────────────────────────────────────────────────────────────

it('happy path: stores ContactMessage and dispatches both emails', function () {
    Mail::fake();

    $component = Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Proba')
        ->set('message', CONTACT_FORM_SHORT_MESSAGE)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    expect(ContactMessage::count())->toBe(1);
    Mail::assertSent(ContactConfirmation::class);
    Mail::assertSent(ContactNotification::class);
    $component->assertSet('statusType', 'success');

    $storedMessage = ContactMessage::query()->firstOrFail();
    expect($storedMessage->user_id)->toBeNull();
});

it('stores matching user_id when submitted email belongs to an existing user', function () {
    Mail::fake();

    $matchingUser = User::factory()->create([
        'email' => CONTACT_FORM_VISITOR_EMAIL,
    ]);

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Gaia')
        ->set('message', CONTACT_FORM_SHORT_MESSAGE)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertSet('statusType', 'success');

    $storedMessage = ContactMessage::query()->firstOrFail();

    expect($storedMessage->user_id)->toBe($matchingUser->id);
});

it('logs matched owner confirmation mail in campaign recipients', function () {
    Mail::fake();

    $matchingUser = User::factory()->create([
        'email' => CONTACT_FORM_VISITOR_EMAIL,
    ]);
    $owner = Owner::factory()->create([
        'user_id' => $matchingUser->id,
        'coprop1_email' => CONTACT_FORM_VISITOR_EMAIL,
    ]);

    session(['contact_form_recent_submissions' => []]);

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Gaia audit test')
        ->set('message', 'Mezu bakarra audit testetarako')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertSet('statusType', 'success');

    $recipient = CampaignRecipient::query()
        ->where('campaign_id', 1)
        ->where('owner_id', $owner->id)
        ->latest('id')
        ->first();

    expect($recipient)->not->toBeNull()
        ->and($recipient?->status)->toBe('sent')
        ->and($recipient?->message_subject)->toBe('[Konfirmazioa] Gaia settings EU');
});

it('prevents duplicate submissions when the same payload is repeated quickly', function () {
    Mail::fake();

    $payload = [
        'name' => CONTACT_FORM_VISITOR_NAME,
        'email' => CONTACT_FORM_VISITOR_EMAIL,
        'subject' => 'Proba',
        'message' => CONTACT_FORM_SHORT_MESSAGE,
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

it('email failure: stores the message and shows warning', function () {
    Log::spy();

    Mail::shouldReceive('to')->andThrow(new Exception('SMTP error'));

    $component = Livewire::test('contact-form')
        ->set('name', 'Ane')
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    expect(ContactMessage::count())->toBe(1);
    $component->assertSet('statusType', 'warning');
});

it('reCAPTCHA external failure: rejects submission', function () {
    config(['app.recaptcha_skip' => false]);
    createSetting('recaptcha_secret_key', 'test-secret');

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
    ]);

    $component = Livewire::test('contact-form')
        ->set('name', 'Ane')
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Gaia')
        ->set('message', 'Mezua')
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'bad-token')
        ->call('submit');

    $component->assertSet('statusType', 'error');
    expect(ContactMessage::count())->toBe(0);
});

it('rejects XSS payload with script tags and does not store the message', function () {
    Mail::fake();

    $payload = '<script>alert("xss")</script>';

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Consulta')
        ->set('message', $payload)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertHasErrors(['message']);

    expect(ContactMessage::count())->toBe(0);
});

it('treats SQL-like payload as plain text in admin inbox', function () {
    Mail::fake();

    $payload = "' OR 1=1 --";

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Consulta SQL')
        ->set('message', $payload)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit');

    $message = ContactMessage::query()->firstOrFail();

    expect($message->message)->toBe($payload);

    Livewire::actingAs(adminUser())
        ->test('admin-message-inbox')
        ->call('setReadFilter', 'all')
        ->call('openMessage', $message->id)
        ->assertSeeText('OR 1=1 --');
});

it('if reCAPTCHA throws an exception, it rejects submission and does not store message', function () {
    config(['app.recaptcha_skip' => false]);
    createSetting('recaptcha_secret_key', 'test-secret');

    Http::fake(function () {
        throw new class('Recaptcha service unavailable') extends RuntimeException {};
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

it('render uses translated checkbox text when settings are missing', function (string $locale) {
    app()->setLocale($locale);

    Livewire::test('contact-form')
        ->assertViewHas('checkboxLabel', __('contact.legal_text'))
        ->assertViewHas('legalModalText', __('contact.legal_text'))
        ->assertViewHas('siteKey', '');
})->with('supported_locales');

it('ignores invalid recent submissions in session and allows submission', function () {
    Mail::fake();

    session(['contact_form_recent_submissions' => 'invalid']);

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Proba')
        ->set('message', CONTACT_FORM_SHORT_MESSAGE)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'skip')
        ->call('submit')
        ->assertSet('statusType', 'success');

    expect(ContactMessage::count())->toBe(1);
});

it('accepts submission when recaptcha secret key is missing even if recaptcha_skip is false', function () {
    Mail::fake();
    config(['app.recaptcha_skip' => false]);

    Livewire::test('contact-form')
        ->set('name', CONTACT_FORM_VISITOR_NAME)
        ->set('email', CONTACT_FORM_VISITOR_EMAIL)
        ->set('subject', 'Sin secret')
        ->set('message', CONTACT_FORM_SHORT_MESSAGE)
        ->set('legalAccepted', true)
        ->set('recaptchaToken', 'token-cualquiera')
        ->call('submit')
        ->assertSet('statusType', 'success');

    expect(ContactMessage::count())->toBe(1);
});

it('renders the translated checkbox label and the settings legal text in the modal', function () {
    app()->setLocale('es');
    createSetting('legal_checkbox_text_es', '<p>Texto legal ES desde settings</p>');

    Livewire::test('contact-form')
        ->assertViewHas('checkboxLabel', __('contact.legal_text'))
        ->assertViewHas('legalModalText', '<p>Texto legal ES desde settings</p>');
});
