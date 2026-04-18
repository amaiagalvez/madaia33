<?php

use Livewire\Livewire;
use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

it('renders contact page with responsive max width container', function (string $locale) {
    $response = $this->get(route(SupportedLocales::routeName('contact', $locale)));

    $response->assertOk();
    $response->assertSee('max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14', false);
    $response->assertSee('data-page="contact"', false);
    $response->assertSee(__('contact.title'));
    $response->assertDontSee('data-contact-intro', false);
})->with('supported_locales');

it('renders contact title block above the form', function (string $locale) {
    $response = $this->get(route(SupportedLocales::routeName('contact', $locale)));

    $response->assertOk();
    $response->assertSee('data-page-hero="contact"', false);
    $response->assertSee(__('contact.subtitle'));
    $response->assertSee('wire:submit="submit"', false);
    $response->assertSeeInOrder([
        'data-page-hero="contact"',
        __('contact.subtitle'),
        'wire:submit="submit"',
    ], false);
})->with('supported_locales');

it('renders full width fields with 44px minimum touch height', function () {
    $response = $this->get(route(SupportedLocales::routeName('contact', SupportedLocales::DEFAULT)));

    $response->assertOk();
    $response->assertSee('block w-full min-h-11 rounded-md border', false);
});

it('renders submit button full width on mobile and auto on desktop', function () {
    $response = $this->get(route(SupportedLocales::routeName('contact', SupportedLocales::DEFAULT)));

    $response->assertOk();
    $response->assertSee('w-full sm:w-auto min-h-11', false);
});

it('shows validation errors when submitting empty form', function () {
    Livewire::test('contact-form')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'subject', 'message', 'legalAccepted'])
        ->assertSee(__('contact.validation.name_required'))
        ->assertSee(__('contact.validation.email_required'))
        ->assertSee(__('contact.validation.subject_required'))
        ->assertSee(__('contact.validation.message_required'));
});

it('renders validation error classes with stable spacing', function () {
    Livewire::test('contact-form')
        ->call('submit')
        ->assertSeeHtml('text-red-600 text-sm mt-1');
});

it('associates counters and legal modal text accessibly in contact form', function () {
    Livewire::test('contact-form')
        ->assertSeeHtml('aria-describedby="contact-subject-counter"')
        ->assertSeeHtml('aria-describedby="contact-message-counter"')
        ->assertSeeHtml('aria-labelledby="contact-legal-label"')
        ->assertSeeHtml('aria-labelledby="contact-legal-modal-title"')
        ->assertSeeHtml('aria-describedby="contact-legal-modal-content"');
});

it('includes recaptcha adaptive action for narrow screens', function () {
    createSetting('recaptcha_site_key', 'test-site-key');

    $response = $this->get(route(SupportedLocales::routeName('contact', SupportedLocales::DEFAULT)));

    $response->assertOk();
    $response->assertSee('contact_compact', false);
});
