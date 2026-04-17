<?php

// Feature: profile contact modal
// Validates: Profile page contact form modal — sends ContactConfirmation to user
// and ContactNotification to admin, with PERFIL prefix only in admin subject.

use App\Models\User;
use Livewire\Livewire;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;

beforeEach(function () {
    config(['app.recaptcha_skip' => true]);
    createSetting('admin_email', 'admin@example.com');
    createSetting('from_address', 'mailhog@example.test');
    createSetting('from_name', 'Madaia 33');
    createSetting('smtp_host', 'smtp.example.test');
    createSetting('smtp_port', '2525');
    createSetting('smtp_username', 'smtp-user');
    createSetting('smtp_password', app(ConfiguredMailSettings::class)->storeValue('smtp_password', 'smtp-secret'));
    createSetting('smtp_encryption', 'tls');
    createSetting('contact_form_subject_eu', 'Gaia EU');
    createSetting('contact_form_subject_es', 'Asunto ES');
});

it('opens and closes the modal', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->assertSet('showModal', false)
        ->call('open')
        ->assertSet('showModal', true)
        ->call('close')
        ->assertSet('showModal', false);
});

it('requires message to submit', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['message' => 'required']);
});

it('stores a ContactMessage on successful submission', function () {
    Mail::fake();
    $user = User::factory()->create(['name' => 'Ane Test', 'email' => 'ane@test.com']);

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->set('message', 'Nire datuak ez daude ondo.')
        ->call('submit');

    expect(ContactMessage::count())->toBe(1);
    $msg = ContactMessage::query()->firstOrFail();
    expect($msg->name)->toBe('Ane Test');
    expect($msg->email)->toBe('ane@test.com');
    expect($msg->user_id)->toBe($user->id);
    expect($msg->subject)->toContain(__('profile.contact_modal.message_subject'));
    expect($msg->message)->toBe('Nire datuak ez daude ondo.');
});

it('sends ContactConfirmation to the logged-in user', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'ane@test.com']);

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->set('message', 'Proba mezua.')
        ->call('submit');

    Mail::assertSent(ContactConfirmation::class, fn($mail) => $mail->hasTo('ane@test.com'));
});

it('sends ContactNotification to admin email from settings', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'ane@test.com']);

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->set('message', 'Proba mezua.')
        ->call('submit');

    Mail::assertSent(ContactNotification::class, fn($mail) => $mail->hasTo('admin@example.com'));
});

it('prepends PERFIL prefix only in admin notification subject', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'ane@test.com']);

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->set('message', 'Proba mezua.')
        ->call('submit');

    Mail::assertSent(ContactNotification::class, fn($mail) => str_starts_with($mail->messageSubject, '[' . __('profile.contact_modal.message_subject') . ']'));
    Mail::assertSent(ContactConfirmation::class, fn($mail) => !str_starts_with($mail->messageSubject, '[' . __('profile.contact_modal.message_subject') . ']'));
});

it('resets fields and closes modal after successful submission', function () {
    Mail::fake();
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->set('message', 'Proba mezua.')
        ->call('submit')
        ->assertSet('message', '')
        ->assertSet('showModal', false);
});

it('renders dialog labelling and message focus hook when opened', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('profile-contact-modal')
        ->call('open')
        ->assertSeeHtml('aria-labelledby="profile-contact-modal-title"')
        ->assertSeeHtml('aria-describedby="profile-contact-modal-description"');
});
