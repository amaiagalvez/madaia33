<?php

// Feature: community-web, Tarea 12: Panel de administración — Configuración
// Valida: Requisitos 11.6, 12.4, 13.4

use App\Models\User;
use App\Models\Image;
use App\Models\Notice;
use Livewire\Livewire;
use App\Models\ContactMessage;

// ─────────────────────────────────────────────────────────────────────────────
// AdminSettings — guardar configuración
// ─────────────────────────────────────────────────────────────────────────────

it('guarda el email de admin en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->set('adminEmail', 'admin@example.com')
        ->call('save');

    expect(settingValue('admin_email'))->toBe('admin@example.com');
});

it('guarda las claves reCAPTCHA en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->set('adminEmail', 'admin@example.com')
        ->set('recaptchaSiteKey', 'site-key-123')
        ->set('recaptchaSecretKey', 'secret-key-456')
        ->call('save');

    expect(settingValue('recaptcha_site_key'))->toBe('site-key-123');
    expect(settingValue('recaptcha_secret_key'))->toBe('secret-key-456');
});

it('guarda el texto legal y la URL en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->set('adminEmail', 'admin@example.com')
        ->set('legalCheckboxTextEu', 'Pribatutasun-politika onartzen dut')
        ->set('legalCheckboxTextEs', 'Acepto la política de privacidad')
        ->set('legalUrl', 'https://example.com/privacidad')
        ->call('save');

    expect(settingValue('legal_checkbox_text_eu'))->toBe('Pribatutasun-politika onartzen dut');
    expect(settingValue('legal_checkbox_text_es'))->toBe('Acepto la política de privacidad');
    expect(settingValue('legal_url'))->toBe('https://example.com/privacidad');
});

it('el campo recaptcha_secret_key se renderiza como type=password', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('type="password"');
});

it('renderiza un editor enriquecido para los textos legales', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('contenteditable="true"')
        ->assertSeeHtml('role="toolbar"');
});

it('rechaza scripts en los textos legales de settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->set('adminEmail', 'admin@example.com')
        ->set('legalCheckboxTextEu', '<script>alert(1)</script>')
        ->call('save')
        ->assertHasErrors(['legalCheckboxTextEu']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Dashboard — estadísticas reales
// ─────────────────────────────────────────────────────────────────────────────

it('el dashboard muestra estadísticas reales', function () {
    $user = User::factory()->create();

    Notice::factory()->create(['is_public' => true]);
    Notice::factory()->create(['is_public' => true]);
    Notice::factory()->create(['is_public' => false]);
    Image::factory()->create();
    ContactMessage::factory()->unread()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('2')   // published notices
        ->assertSee('3')   // total notices
        ->assertSee('1');  // images and unread messages
});
