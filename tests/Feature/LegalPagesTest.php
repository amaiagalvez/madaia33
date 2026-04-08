<?php

// Feature: community-web, Tarea 13: Páginas legales, SEO y seguridad
// Valida: Requisitos 15.1–15.4

use App\Models\User;
use Livewire\Livewire;
use App\Models\Setting;

// ─────────────────────────────────────────────────────────────────────────────
// Acceso público a páginas legales
// ─────────────────────────────────────────────────────────────────────────────

it('la página de política de privacidad es accesible públicamente', function () {
    $this->get(route('privacy-policy'))->assertOk();
});

it('la página de aviso legal es accesible públicamente', function () {
    $this->get(route('legal-notice'))->assertOk();
});

it('las páginas legales reutilizan la misma vista pública con contenido diferenciado', function () {
    createSetting('legal_page_privacy_policy_eu', 'Pribatutasun eduki partekatua');
    createSetting('legal_page_legal_notice_eu', 'Lege ohar eduki partekatua');

    $privacy = $this->get(route('privacy-policy'));

    $privacy->assertOk()
        ->assertViewIs('public.legal-page')
        ->assertSee('data-legal-page="privacy-policy"', false)
        ->assertSee('Pribatutasun eduki partekatua');

    $legal = $this->get(route('legal-notice'));

    $legal->assertOk()
        ->assertViewIs('public.legal-page')
        ->assertSee('data-legal-page="legal-notice"', false)
        ->assertSee('Lege ohar eduki partekatua');
});

// ─────────────────────────────────────────────────────────────────────────────
// Admin puede editar el contenido de páginas legales
// ─────────────────────────────────────────────────────────────────────────────

it('el admin puede guardar el contenido de política de privacidad', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('adminEmail', 'admin@example.com')
        ->set('privacyContentEu', 'Pribatutasun politika berria')
        ->set('privacyContentEs', 'Nueva política de privacidad')
        ->call('save');

    expect(settingValue('legal_page_privacy_policy_eu'))->toBe('Pribatutasun politika berria');
    expect(settingValue('legal_page_privacy_policy_es'))->toBe('Nueva política de privacidad');
});

it('el admin puede guardar el contenido de aviso legal', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('adminEmail', 'admin@example.com')
        ->set('legalNoticeContentEu', 'Lege oharra berria')
        ->set('legalNoticeContentEs', 'Nuevo aviso legal')
        ->call('save');

    expect(settingValue('legal_page_legal_notice_eu'))->toBe('Lege oharra berria');
    expect(settingValue('legal_page_legal_notice_es'))->toBe('Nuevo aviso legal');
});

it('el componente carga el contenido existente al montar', function () {
    createSetting('legal_page_privacy_policy_eu', 'Eduki existentea');
    createSetting('legal_page_privacy_policy_es', 'Contenido existente');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSet('privacyContentEu', 'Eduki existentea')
        ->assertSet('privacyContentEs', 'Contenido existente');
});

it('la antigua ruta admin de páginas legales ya no existe', function () {
    $this->get('/admin/paginas-legales')->assertNotFound();
});
