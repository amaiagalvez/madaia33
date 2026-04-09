<?php

// Feature: community-web, Tarea 12: Panel de administración — Configuración
// Valida: Requisitos 11.6, 12.4, 13.4

use App\Models\User;
use App\Models\Image;
use App\Models\Notice;
use Livewire\Livewire;
use App\Mail\TestEmail;
use App\Models\Setting;
use App\SupportedLocales;
use App\Models\ContactMessage;
use App\Livewire\AdminSettings;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;
use App\Validations\AdminSettingsValidation;

const ADMIN_SETTINGS_EMAIL = 'admin@example.com';

// ─────────────────────────────────────────────────────────────────────────────
// AdminSettings — guardar configuración
// ─────────────────────────────────────────────────────────────────────────────

it('guarda el email de admin en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->set('adminEmail', ADMIN_SETTINGS_EMAIL)
        ->call('save');

    expect(settingValue('admin_email'))->toBe(ADMIN_SETTINGS_EMAIL);
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
        ->set('historyTextEu', '<p>Historia EU</p>')
        ->set('historyTextEs', '<p>Historia ES</p>')
        ->set('privacyContentEu', '<p>Privacidad EU</p>')
        ->set('privacyContentEs', '<p>Privacidad ES</p>')
        ->set('legalNoticeContentEu', '<p>Aviso EU</p>')
        ->set('legalNoticeContentEs', '<p>Aviso ES</p>')
        ->call('save');

    expect(settingValue('home_history_text_eu'))->toBe('<p>Historia EU</p>')
        ->and(settingValue('home_history_text_es'))->toBe('<p>Historia ES</p>')
        ->and(settingValue('legal_page_privacy_policy_eu'))->toBe('<p>Privacidad EU</p>')
        ->and(settingValue('legal_page_privacy_policy_es'))->toBe('<p>Privacidad ES</p>')
        ->and(settingValue('legal_page_legal_notice_eu'))->toBe('<p>Aviso EU</p>')
        ->and(settingValue('legal_page_legal_notice_es'))->toBe('<p>Aviso ES</p>');
});

it('guarda el texto legal en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->set('adminEmail', ADMIN_SETTINGS_EMAIL)
        ->set('legalCheckboxTextEu', 'Pribatutasun-politika onartzen dut')
        ->set('legalCheckboxTextEs', 'Acepto la política de privacidad')
        ->call('save');

    expect(settingValue('legal_checkbox_text_eu'))->toBe('Pribatutasun-politika onartzen dut');
    expect(settingValue('legal_checkbox_text_es'))->toBe('Acepto la política de privacidad');
});

it('guarda la configuración de correo en la tabla settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_EMAIL_CONFIGURATION)
        ->set('emailFromAddress', 'noreply@example.com')
        ->set('emailFromName', 'Madaia 33')
        ->set('smtpHost', 'smtp.example.com')
        ->set('smtpPort', '587')
        ->set('smtpUsername', 'smtp-user')
        ->set('smtpPassword', 'smtp-secret')
        ->set('smtpEncryption', 'tls')
        ->set('emailLegalTextEu', '<p>Ohar legala</p>')
        ->set('emailLegalTextEs', '<p>Aviso legal</p>')
        ->call('save');

    $configuredMailSettings = app(ConfiguredMailSettings::class);

    expect(settingValue('from_address'))->toBe('noreply@example.com')
        ->and(settingValue('from_name'))->toBe('Madaia 33')
        ->and(settingValue('smtp_host'))->toBe('smtp.example.com')
        ->and(settingValue('smtp_port'))->toBe('587')
        ->and(settingValue('smtp_username'))->toBe('smtp-user')
        ->and(settingValue('smtp_password'))->not->toBe('smtp-secret')
        ->and($configuredMailSettings->displayValue('smtp_password', (string) settingValue('smtp_password')))->toBe('smtp-secret')
        ->and(settingValue('smtp_encryption'))->toBe('tls')
        ->and(settingValue('legal_text_eu'))->toBe('<p>Ohar legala</p>')
        ->and(settingValue('legal_text_es'))->toBe('<p>Aviso legal</p>');
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
        ->assertSeeHtml('historyTextEu')
        ->assertSeeHtml('historyTextEs')
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
        ->assertSee(SupportedLocales::BASQUE)
        ->assertSee(SupportedLocales::SPANISH)
        ->assertSeeHtml('data-bilingual-field="historyTextEu"')
        ->assertSeeHtml('data-bilingual-field="privacyContentEu"')
        ->assertSeeHtml('data-bilingual-field="legalNoticeContentEu"');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->assertSee(SupportedLocales::BASQUE)
        ->assertSee(SupportedLocales::SPANISH)
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

it('renderiza los campos de configuración de correo dentro de su sección', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_EMAIL_CONFIGURATION)
        ->assertSeeHtml('id="emailFromAddress"')
        ->assertSeeHtml('id="emailFromName"')
        ->assertSeeHtml('id="smtpHost"')
        ->assertSeeHtml('id="smtpPort"')
        ->assertSeeHtml('id="smtpUsername"')
        ->assertSeeHtml('id="smtpPassword"')
        ->assertSeeHtml('id="smtpEncryption"')
        ->assertSeeHtml('data-bilingual-field="emailLegalTextEu"');
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
        ->set('adminEmail', ADMIN_SETTINGS_EMAIL)
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

    $settings->each(fn (Setting $s) => expect(Setting::allowedSections())->toContain($s->section));
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
        ->toContain(Setting::SECTION_EMAIL_CONFIGURATION)
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
        ->assertSeeHtml('bg-[#edd2c7] text-[#793d3d]')
        ->assertSeeHtml('text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]');
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

it('expone reglas y mensajes de validación de AdminSettingsValidation', function () {
    $component = new class extends AdminSettings {
        /**
         * @return array<string, mixed>
         */
        public function exposedRules(): array
        {
            return $this->rules();
        }

        /**
         * @return array<string, mixed>
         */
        public function exposedMessages(): array
        {
            return $this->messages();
        }
    };

    expect($component->exposedRules())->toEqual(AdminSettingsValidation::rules())
        ->and($component->exposedMessages())->toBe(AdminSettingsValidation::messages());
});

// ─────────────────────────────────────────────────────────────────────────────
// T017 — Backfill completeness
// ─────────────────────────────────────────────────────────────────────────────

it('los keys del seeder se pueden asignar a contact_form sin conflicto', function () {
    $knownKeys = [
        'legal_checkbox_text_eu',
        'legal_checkbox_text_es',
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
        'home_history_text_eu',
        'home_history_text_es',
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

// ─────────────────────────────────────────────────────────────────────────────
// Test Email Features
// ─────────────────────────────────────────────────────────────────────────────

it('openTestEmailModal abre el modal y resetea el formulario', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->assertSet('showTestEmailModal', true)
        ->assertSet('testEmailAddress', '')
        ->assertSet('testEmailStatus', '');
});

it('closeTestEmailModal cierra el modal y resetea el estado', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->call('closeTestEmailModal')
        ->assertSet('showTestEmailModal', false)
        ->assertSet('testEmailAddress', '')
        ->assertSet('testEmailStatus', '');
});

it('sendTestEmail valida que testEmailAddress sea un email válido', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->set('testEmailAddress', 'not-an-email')
        ->call('sendTestEmail')
        ->assertHasErrors(['testEmailAddress']);
});

it('sendTestEmail requiere SMTP configurado (smtp_host no vacío)', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->set('testEmailAddress', 'test@example.com')
        ->set('smtpHost', '')
        ->call('sendTestEmail')
        ->assertSet('testEmailStatus', 'error');
});

it('sendTestEmail envía un email de prueba con la configuración SMTP', function () {
    $user = User::factory()->create();

    createSetting('from_address', 'sender@example.com');
    createSetting('from_name', 'Test Sender');
    createSetting('smtp_host', 'smtp.mailtrap.io');
    createSetting('smtp_port', '587');
    createSetting('smtp_username', 'user');
    createSetting('smtp_password', encrypt('password'));
    createSetting('smtp_encryption', 'tls');

    Mail::fake();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_EMAIL_CONFIGURATION)
        ->set('emailFromAddress', 'sender@example.com')
        ->set('emailFromName', 'Test Sender')
        ->set('smtpHost', 'smtp.mailtrap.io')
        ->set('smtpPort', '587')
        ->set('smtpUsername', 'user')
        ->set('smtpPassword', 'password')
        ->set('smtpEncryption', 'tls')
        ->call('openTestEmailModal')
        ->set('testEmailAddress', 'recipient@example.com')
        ->call('sendTestEmail')
        ->assertSet('showTestEmailModal', false);

    Mail::assertSent(TestEmail::class, function ($mail) {
        return $mail->hasTo('recipient@example.com');
    });
});
