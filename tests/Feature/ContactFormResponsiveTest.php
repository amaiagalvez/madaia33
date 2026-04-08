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

it('renders full width fields with 44px minimum touch height', function (string $locale) {
    $response = $this->get(route(SupportedLocales::routeName('contact', $locale)));

    $response->assertOk();
    $response->assertSee('block w-full min-h-11 rounded-md border', false);
})->with('supported_locales');

it('renders submit button full width on mobile and auto on desktop', function (string $locale) {
    $response = $this->get(route(SupportedLocales::routeName('contact', $locale)));

    $response->assertOk();
    $response->assertSee('w-full sm:w-auto min-h-11', false);
})->with('supported_locales');

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

it('includes recaptcha adaptive action for narrow screens', function (string $locale) {
    createSetting('recaptcha_site_key', 'test-site-key');

    $response = $this->get(route(SupportedLocales::routeName('contact', $locale)));

    $response->assertOk();
    $response->assertSee('contact_compact', false);
})->with('supported_locales');
