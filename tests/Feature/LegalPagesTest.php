<?php

// Feature: community-web, Task 13: Legal pages, SEO, and security
// Validates: Requirements 15.1–15.4

use App\Models\User;
use Livewire\Livewire;
use App\Models\Setting;
use App\SupportedLocales;

dataset('supported_locales', SupportedLocales::all());

// ─────────────────────────────────────────────────────────────────────────────
// Public access to legal pages
// ─────────────────────────────────────────────────────────────────────────────

it('privacy policy page is publicly accessible', function (string $locale) {
    test()->get(route(SupportedLocales::routeName('privacy-policy', $locale)))->assertOk();
})->with('supported_locales');

it('legal notice page is publicly accessible', function (string $locale) {
    test()->get(route(SupportedLocales::routeName('legal-notice', $locale)))->assertOk();
})->with('supported_locales');

it('legal pages reuse the same public view with different content', function (string $locale) {
    createSetting('legal_page_privacy_policy_eu', 'Pribatutasun eduki partekatua');
    createSetting('legal_page_legal_notice_eu', 'Lege ohar eduki partekatua');

    $privacy = test()->get(route(SupportedLocales::routeName('privacy-policy', $locale)));

    $privacy->assertOk()
        ->assertViewIs('public.legal-page')
        ->assertSee('data-legal-page="privacy-policy"', false)
        ->assertSee('Pribatutasun eduki partekatua');

    $legal = test()->get(route(SupportedLocales::routeName('legal-notice', $locale)));

    $legal->assertOk()
        ->assertViewIs('public.legal-page')
        ->assertSee('data-legal-page="legal-notice"', false)
        ->assertSee('Lege ohar eduki partekatua');
})->with('supported_locales');

// ─────────────────────────────────────────────────────────────────────────────
// Admin can edit legal pages content
// ─────────────────────────────────────────────────────────────────────────────

it('admin can save privacy policy content', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('frontSiteName', 'Madaia 33')
        ->set('frontPrimaryEmail', 'info@example.com')
        ->set('privacyContentEu', 'Pribatutasun politika berria')
        ->set('privacyContentEs', 'Nueva política de privacidad')
        ->call('save');

    expect(settingValue('legal_page_privacy_policy_eu'))->toBe('Pribatutasun politika berria');
    expect(settingValue('legal_page_privacy_policy_es'))->toBe('Nueva política de privacidad');
});

it('admin can save legal notice content', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('frontSiteName', 'Madaia 33')
        ->set('frontPrimaryEmail', 'info@example.com')
        ->set('legalNoticeContentEu', 'Lege oharra berria')
        ->set('legalNoticeContentEs', 'Nuevo aviso legal')
        ->call('save');

    expect(settingValue('legal_page_legal_notice_eu'))->toBe('Lege oharra berria');
    expect(settingValue('legal_page_legal_notice_es'))->toBe('Nuevo aviso legal');
});

it('component loads existing content on mount', function () {
    createSetting('legal_page_privacy_policy_eu', 'Eduki existentea');
    createSetting('legal_page_privacy_policy_es', 'Contenido existente');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSet('privacyContentEu', 'Eduki existentea')
        ->assertSet('privacyContentEs', 'Contenido existente');
});

it('legacy admin route for legal pages no longer exists', function () {
    test()->get('/admin/paginas-legales')->assertNotFound();
});
