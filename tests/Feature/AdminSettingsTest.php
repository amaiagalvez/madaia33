<?php

// Feature: community-web, Tarea 12: Panel de administración — Configuración
// Valida: Requisitos 11.6, 12.4, 13.4

use App\Models\User;
use App\Models\Image;
use App\Models\Notice;
use Livewire\Livewire;
use App\Models\Setting;
use App\Models\ContactMessage;

// ─────────────────────────────────────────────────────────────────────────────
// AdminSettings — guardar configuración
// ─────────────────────────────────────────────────────────────────────────────

it('guarda el email de admin en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->set('adminEmail', 'admin@example.com')
        ->call('save');

    expect(settingValue('admin_email'))->toBe('admin@example.com');
});

it('guarda las claves reCAPTCHA en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->set('recaptchaSiteKey', 'site-key-123')
        ->call('save');

    expect(settingValue('recaptcha_site_key'))->toBe('site-key-123');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->set('recaptchaSecretKey', 'secret-key-456')
        ->call('save');

    expect(settingValue('recaptcha_secret_key'))->toBe('secret-key-456');
});

it('guarda el contenido de las páginas legales dentro de la sección front', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('privacyContentEu', '<p>Privacidad EU</p>')
        ->set('privacyContentEs', '<p>Privacidad ES</p>')
        ->set('legalNoticeContentEu', '<p>Aviso EU</p>')
        ->set('legalNoticeContentEs', '<p>Aviso ES</p>')
        ->call('save');

    expect(settingValue('legal_page_privacy_policy_eu'))->toBe('<p>Privacidad EU</p>')
        ->and(settingValue('legal_page_privacy_policy_es'))->toBe('<p>Privacidad ES</p>')
        ->and(settingValue('legal_page_legal_notice_eu'))->toBe('<p>Aviso EU</p>')
        ->and(settingValue('legal_page_legal_notice_es'))->toBe('<p>Aviso ES</p>');
});

it('guarda el texto legal y la URL en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
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
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->assertSeeHtml('type="password"');
});

it('renderiza un editor enriquecido para los textos legales', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('contenteditable="true"')
        ->assertSeeHtml('role="toolbar"');
});

it('no carga flux ni fuentes externas en la vista admin de configuracion', function () {
    $user = User::factory()->create();

    test()->actingAs($user)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertDontSee('fonts.bunny.net', false)
        ->assertDontSee('/flux/flux.js', false);
});

it('renderiza también los editores ricos de política de privacidad y aviso legal en front', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->assertSeeHtml('privacyContentEu')
        ->assertSeeHtml('privacyContentEs')
        ->assertSeeHtml('legalNoticeContentEu')
        ->assertSeeHtml('legalNoticeContentEs');
});

it('renderiza tabs de idioma EUS/CAS en los bloques bilingües de settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->assertSee(__('admin.settings_form.language_tab_eus'))
        ->assertSee(__('admin.settings_form.language_tab_cas'))
        ->assertSeeHtml('data-bilingual-field="privacyContentEu"')
        ->assertSeeHtml('data-bilingual-field="legalNoticeContentEu"');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->assertSee(__('admin.settings_form.language_tab_eus'))
        ->assertSee(__('admin.settings_form.language_tab_cas'))
        ->assertSeeHtml('data-bilingual-field="legalCheckboxTextEu"');
});

it('renderiza los campos recaptcha dentro de su propia sección', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->assertSeeHtml('id="recaptchaSiteKey"')
        ->assertSeeHtml('id="recaptchaSecretKey"');
});

it('mantiene visible el botón guardar en la sección front', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->assertSee(__('general.buttons.save'))
        ->assertSeeHtml('type="submit"');
});

it('rechaza scripts en los textos legales de settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
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

    test()->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('2')   // published notices
        ->assertSee('3')   // total notices
        ->assertSee('1');  // images and unread messages
});

// ─────────────────────────────────────────────────────────────────────────────
// T009 — Section integrity
// ─────────────────────────────────────────────────────────────────────────────

it('los settings creados con factory tienen sección válida', function () {
    $settings = Setting::factory()->count(4)->create();

    $settings->each(fn(Setting $s) => expect(Setting::allowedSections())->toContain($s->section));
});

// ─────────────────────────────────────────────────────────────────────────────
// T010 — Tab rendering and alphabetical ordering
// ─────────────────────────────────────────────────────────────────────────────

it('los tabs de secciones se muestran en orden alfabético', function () {
    $user = User::factory()->create();
    Setting::factory()->create(['key' => 'front_img', 'section' => Setting::SECTION_FRONT]);

    $component = Livewire::actingAs($user)->test('admin-settings');

    $sections = $component->get('availableSections');

    expect($sections)
        ->toContain(Setting::SECTION_CONTACT_FORM)
        ->toContain(Setting::SECTION_FRONT)
        ->toContain(Setting::SECTION_RECAPTCHA)
        ->and($sections)->toBe(collect($sections)->sort()->values()->all());
});

it('la sección activa inicial es la primera sección en orden alfabético', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test('admin-settings');

    $sections = $component->get('availableSections');
    $activeSection = $component->get('activeSection');

    expect($activeSection)->toBe($sections[0]);
});

it('los tabs renderizan etiquetas de sección con wire:click', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('wire:click')
        ->assertSeeHtml('bg-amber-100 text-amber-900')
        ->assertSeeHtml('text-stone-600 hover:bg-amber-50 hover:text-stone-900');
});

it('los campos del formulario fuerzan contraste legible en fondo blanco', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('bg-white px-3 py-2 text-sm text-stone-900')
        ->assertSeeHtml('placeholder:text-stone-400');
});

// ─────────────────────────────────────────────────────────────────────────────
// T011 — Section-scoped save
// ─────────────────────────────────────────────────────────────────────────────

it('save solo escribe los settings de la sección activa sin afectar otras secciones', function () {
    $user = User::factory()->create();

    createSetting('admin_email', 'original@example.com');
    createSetting('recaptcha_site_key', 'site-key-original');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->call('save');

    expect(settingValue('admin_email'))->toBe('original@example.com')
        ->and(settingValue('recaptcha_site_key'))->toBe('site-key-original');
});

it('save de recaptcha solo escribe sus claves sin afectar contact_form', function () {
    $user = User::factory()->create();

    createSetting('admin_email', 'contact@example.com');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->set('recaptchaSiteKey', 'new-site-key')
        ->call('save');

    expect(settingValue('recaptcha_site_key'))->toBe('new-site-key')
        ->and(settingValue('admin_email'))->toBe('contact@example.com');
});

// ─────────────────────────────────────────────────────────────────────────────
// T017 — Backfill completeness
// ─────────────────────────────────────────────────────────────────────────────

it('los keys del seeder se pueden asignar a contact_form sin conflicto', function () {
    $knownKeys = [
        'legal_checkbox_text_eu',
        'legal_checkbox_text_es',
        'legal_url',
        'admin_email',
    ];

    foreach ($knownKeys as $key) {
        Setting::factory()->create(['key' => $key, 'section' => Setting::SECTION_CONTACT_FORM]);
    }

    $sections = Setting::whereIn('key', $knownKeys)->pluck('section')->unique()->all();

    expect($sections)->toBe([Setting::SECTION_CONTACT_FORM]);
});

it('los keys de páginas legales se pueden asignar a la sección front sin conflicto', function () {
    $knownKeys = [
        'legal_page_privacy_policy_eu',
        'legal_page_privacy_policy_es',
        'legal_page_legal_notice_eu',
        'legal_page_legal_notice_es',
    ];

    foreach ($knownKeys as $key) {
        Setting::factory()->create(['key' => $key, 'section' => Setting::SECTION_FRONT]);
    }

    $sections = Setting::whereIn('key', $knownKeys)->pluck('section')->unique()->all();

    expect($sections)->toBe([Setting::SECTION_FRONT]);
});

it('los keys de recaptcha se pueden asignar a la sección recaptcha sin conflicto', function () {
    $knownKeys = [
        'recaptcha_site_key',
        'recaptcha_secret_key',
    ];

    foreach ($knownKeys as $key) {
        Setting::factory()->create(['key' => $key, 'section' => Setting::SECTION_RECAPTCHA]);
    }

    $sections = Setting::whereIn('key', $knownKeys)->pluck('section')->unique()->all();

    expect($sections)->toBe([Setting::SECTION_RECAPTCHA]);
});

// ─────────────────────────────────────────────────────────────────────────────
// T018 — Fallback to general
// ─────────────────────────────────────────────────────────────────────────────

it('Setting::normalizeSection devuelve general para secciones desconocidas', function () {
    expect(Setting::normalizeSection('unknown'))->toBe(Setting::SECTION_GENERAL)
        ->and(Setting::normalizeSection(null))->toBe(Setting::SECTION_GENERAL)
        ->and(Setting::normalizeSection(''))->toBe(Setting::SECTION_GENERAL);
});

// ─────────────────────────────────────────────────────────────────────────────
// T023 — Section guard on setSection
// ─────────────────────────────────────────────────────────────────────────────

it('setSection ignora secciones no presentes en availableSections', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test('admin-settings');
    $originalSection = $component->get('activeSection');

    $component->call('setSection', 'nonexistent_section');

    expect($component->get('activeSection'))->toBe($originalSection);
});

// ─────────────────────────────────────────────────────────────────────────────
// T033 — SC-004: New section tab renders without structural UI changes
// ─────────────────────────────────────────────────────────────────────────────

it('añadir datos en una nueva sección renderiza su tab sin cambios estructurales en el componente', function () {
    $user = User::factory()->create();
    Setting::factory()->create(['key' => 'gallery_columns', 'section' => Setting::SECTION_GALLERY]);

    $component = Livewire::actingAs($user)->test('admin-settings');

    $sections = $component->get('availableSections');

    expect($sections)->toContain(Setting::SECTION_GALLERY)
        ->and($component->get('activeSection'))->toBe($sections[0]);

    $component->assertSeeHtml(Setting::SECTION_GALLERY);
});
