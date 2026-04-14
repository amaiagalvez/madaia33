<?php

// Feature: community-web, Task 12: Admin panel — Settings
// Validates: Requirements 11.6, 12.4, 13.4

use App\Models\Image;
use Database\Seeders\DevSeeder;
use App\Models\Notice;
use Livewire\Livewire;
use App\Mail\TestEmail;
use App\Models\Setting;
use App\SupportedLocales;
use App\Models\ContactMessage;
use App\Livewire\AdminSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Support\ConfiguredMailSettings;
use Illuminate\Support\Facades\Storage;
use App\Validations\AdminSettingsValidation;

const ADMIN_SETTINGS_EMAIL = 'admin@example.com';

// ─────────────────────────────────────────────────────────────────────────────
// AdminSettings — save configuration
// ─────────────────────────────────────────────────────────────────────────────

it('stores admin email in settings table', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->set('adminEmail', ADMIN_SETTINGS_EMAIL)
        ->call('save');

    expect(settingValue('admin_email'))->toBe(ADMIN_SETTINGS_EMAIL);
});

it('stores reCAPTCHA keys in settings table', function () {
    $user = adminUser();

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

it('stores legal pages content inside front section', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('frontSiteName', 'Madaia 33')
        ->set('frontPrimaryEmail', 'front@example.com')
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

it('stores new front fields for brand, email, photos text, and cookies', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('frontSiteName', 'Comunidad Madaia')
        ->set('frontPrimaryEmail', 'front@example.com')
        ->set('frontLogoImagePath', 'branding/logo.png')
        ->set('frontPhotoRequestTextEu', 'Bidali argazkiak helbide honetara: :email')
        ->set('frontPhotoRequestTextEs', 'Envia fotos a este email: :email')
        ->set('cookiePolicyContentEu', '<p>Cookie politika EU</p>')
        ->set('cookiePolicyContentEs', '<p>Politica de cookies ES</p>')
        ->call('save');

    expect(settingValue('front_site_name'))->toBe('Comunidad Madaia')
        ->and(settingValue('front_primary_email'))->toBe('front@example.com')
        ->and(settingValue('front_logo_image_path'))->toBe('branding/logo.png')
        ->and(settingValue('front_photo_request_text_eu'))->toBe('Bidali argazkiak helbide honetara: :email')
        ->and(settingValue('front_photo_request_text_es'))->toBe('Envia fotos a este email: :email')
        ->and(settingValue('legal_page_cookie_policy_eu'))->toBe('<p>Cookie politika EU</p>')
        ->and(settingValue('legal_page_cookie_policy_es'))->toBe('<p>Politica de cookies ES</p>');
});

it('uploads and stores the front logo image in public storage', function () {
    Storage::fake('public');

    $user = adminUser();
    $logo = UploadedFile::fake()->image('front-logo.png', 320, 120);

    $component = Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->set('frontSiteName', 'Comunidad Madaia')
        ->set('frontPrimaryEmail', 'front@example.com')
        ->set('frontLogoImage', $logo)
        ->call('save');

    $storedPath = (string) settingValue('front_logo_image_path');

    expect($storedPath)->toStartWith('branding/')
        ->and(Storage::disk('public')->exists($storedPath))->toBeTrue();

    $component->assertSet('frontLogoImage', null);
});

it('stores legal text in settings table', function () {
    $user = adminUser();

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

it('stores contact subjects by language in contact_form', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->set('adminEmail', ADMIN_SETTINGS_EMAIL)
        ->set('contactFormSubjectEu', 'Gaia pertsonalizatua EU')
        ->set('contactFormSubjectEs', 'Asunto personalizado ES')
        ->call('save');

    expect(settingValue('contact_form_subject_eu'))->toBe('Gaia pertsonalizatua EU')
        ->and(settingValue('contact_form_subject_es'))->toBe('Asunto personalizado ES');
});

it('dev seeder configures local email settings for mailhog', function () {
    artisan('db:seed', ['--class' => DevSeeder::class])->assertExitCode(0);

    expect(settingValue('from_address'))->toBe('info@mailhog.local')
        ->and(settingValue('from_name'))->toBe('Komunitatea Local')
        ->and(settingValue('smtp_host'))->toBe('mailhog')
        ->and(settingValue('smtp_port'))->toBe('1025')
        ->and(settingValue('smtp_username'))->toBe('')
        ->and(settingValue('smtp_encryption'))->toBe('');
});

it('stores mail configuration in settings table', function () {
    $user = adminUser();

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

it('stores new owners email configuration in settings', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_OWNERS)
        ->set('ownersWelcomeSubjectEu', 'Gaia EU')
        ->set('ownersWelcomeSubjectEs', 'Asunto ES')
        ->set('ownersWelcomeTextEu', '<p>Testu EU</p>##info##')
        ->set('ownersWelcomeTextEs', '<p>Texto ES</p>##info##')
        ->call('save');

    expect(settingValue('owners_welcome_subject_eu'))->toBe('Gaia EU')
        ->and(settingValue('owners_welcome_subject_es'))->toBe('Asunto ES')
        ->and(settingValue('owners_welcome_text_eu'))->toBe('<p>Testu EU</p>##info##')
        ->and(settingValue('owners_welcome_text_es'))->toBe('<p>Texto ES</p>##info##');
});

it('stores voting pdf texts in votings settings section', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_VOTINGS)
        ->set('votingsPdfDelegatedTextEu', '<p>Boto delegatua EU</p>')
        ->set('votingsPdfDelegatedTextEs', '<p>Voto delegado ES</p>')
        ->set('votingsPdfInPersonTextEu', '<p>Boto presentziala EU</p>')
        ->set('votingsPdfInPersonTextEs', '<p>Voto presencial ES</p>')
        ->set('votingsExplanationTextEu', '<p>Azalpena EU</p>')
        ->set('votingsExplanationTextEs', '<p>Explicacion ES</p>')
        ->call('save');

    expect(settingValue('votings_pdf_delegated_text_eu'))->toBe('<p>Boto delegatua EU</p>')
        ->and(settingValue('votings_pdf_delegated_text_es'))->toBe('<p>Voto delegado ES</p>')
        ->and(settingValue('votings_pdf_in_person_text_eu'))->toBe('<p>Boto presentziala EU</p>')
        ->and(settingValue('votings_pdf_in_person_text_es'))->toBe('<p>Voto presencial ES</p>')
        ->and(settingValue('votings_explanation_text_eu'))->toBe('<p>Azalpena EU</p>')
        ->and(settingValue('votings_explanation_text_es'))->toBe('<p>Explicacion ES</p>');
});

it('loads votings explanation EU and ES values in settings even with stale cache', function () {
    $user = adminUser();

    createSetting('votings_explanation_text_eu', '<p>Azalpen cache gabekoa EU</p>');
    createSetting('votings_explanation_text_es', '<p>Explicacion sin cache ES</p>');

    Cache::forever('settings:string-values', [
        'votings_explanation_text_eu' => '',
        'votings_explanation_text_es' => '',
    ]);

    Livewire::actingAs($user)
        ->test(AdminSettings::class)
        ->call('setSection', Setting::SECTION_VOTINGS)
        ->assertSet('votingsExplanationTextEu', '<p>Azalpen cache gabekoa EU</p>')
        ->assertSet('votingsExplanationTextEs', '<p>Explicacion sin cache ES</p>');
});

it('saves rich-text values in one submit action', function () {
    $user = adminUser();

    createSetting('votings_explanation_text_eu', '<p>Aurreko balioa EU</p>');
    createSetting('votings_explanation_text_es', '<p>Valor previo ES</p>');

    Livewire::actingAs($user)
        ->test(AdminSettings::class)
        ->call('setSection', Setting::SECTION_VOTINGS)
        ->call('saveWithEditorValues', [
            'votingsExplanationTextEu' => '<p>Balio berria EU</p>',
            'votingsExplanationTextEs' => '<p>Valor nuevo ES</p>',
        ]);

    expect(settingValue('votings_explanation_text_eu'))->toBe('<p>Balio berria EU</p>')
        ->and(settingValue('votings_explanation_text_es'))->toBe('<p>Valor nuevo ES</p>');
});

it('recaptcha_secret_key field renders as type password', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->assertSeeHtml('type="password"');
});

it('renders a rich editor for legal texts', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('contenteditable="true"')
        ->assertSeeHtml('role="toolbar"');
});

it('does not load external fonts in admin settings view', function () {
    $user = adminUser();

    test()->actingAs($user)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertDontSee('fonts.bunny.net', false);
});

it('also renders rich editors for privacy policy and legal notice in front', function () {
    $user = adminUser();

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

it('renders EUS CAS language tabs in bilingual settings blocks', function () {
    $user = adminUser();

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

it('renders recaptcha fields inside their own section', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->assertSeeHtml('id="recaptchaSiteKey"')
        ->assertSeeHtml('id="recaptchaSecretKey"');
});

it('renders mail configuration fields inside their section', function () {
    $user = adminUser();

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

it('renders new owners email fields inside their section', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_OWNERS)
        ->assertSeeHtml('data-bilingual-field="ownersWelcomeSubjectEu"')
        ->assertSeeHtml('data-bilingual-field="ownersWelcomeTextEu"');
});

it('keeps save button visible in front section', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_FRONT)
        ->assertSee(__('general.buttons.save'))
        ->assertSeeHtml('type="submit"');
});

it('rejects scripts in settings legal texts', function () {
    $user = adminUser();

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

it('dashboard shows real statistics', function () {
    $user = adminUser();

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

it('dashboard uses the front logo configured in settings', function () {
    $user = adminUser();

    createSetting('front_logo_image_path', 'branding/front-logo.png');

    test()->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('storage/branding/front-logo.png', false);
});

// ─────────────────────────────────────────────────────────────────────────────
// T009 — Section integrity
// ─────────────────────────────────────────────────────────────────────────────

it('factory-created settings have valid section', function () {
    $settings = Setting::factory()->count(4)->create();

    $settings->each(fn(Setting $s) => expect(Setting::allowedSections())->toContain($s->section));
});

// ─────────────────────────────────────────────────────────────────────────────
// T010 — Tab rendering and alphabetical ordering
// ─────────────────────────────────────────────────────────────────────────────

it('section tabs are shown in alphabetical order', function () {
    $user = adminUser();
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

it('initial active section is the first section in alphabetical order', function () {
    $user = adminUser();

    $component = Livewire::actingAs($user)->test('admin-settings');

    $sections = $component->get('availableSections');
    $activeSection = $component->get('activeSection');

    expect($activeSection)->toBe($sections[0]);
});

it('tabs render section labels with wire click', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('@click.prevent')
        ->assertSeeHtml('bg-[#edd2c7] text-[#793d3d]')
        ->assertSeeHtml('text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]');
});

it('form fields enforce readable contrast on white background', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->assertSeeHtml('bg-white px-3 py-2 text-sm text-stone-900')
        ->assertSeeHtml('placeholder:text-stone-400');
});

// ─────────────────────────────────────────────────────────────────────────────
// T011 — Section-scoped save
// ─────────────────────────────────────────────────────────────────────────────

it('save only writes settings for active section without affecting others', function () {
    $user = adminUser();

    createSetting('admin_email', 'original@example.com');
    createSetting('recaptcha_site_key', 'site-key-original');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_CONTACT_FORM)
        ->call('save');

    expect(settingValue('admin_email'))->toBe('original@example.com')
        ->and(settingValue('recaptcha_site_key'))->toBe('site-key-original');
});

it('recaptcha save only writes its keys without affecting contact_form', function () {
    $user = adminUser();

    createSetting('admin_email', 'contact@example.com');

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('setSection', Setting::SECTION_RECAPTCHA)
        ->set('recaptchaSiteKey', 'new-site-key')
        ->call('save');

    expect(settingValue('recaptcha_site_key'))->toBe('new-site-key')
        ->and(settingValue('admin_email'))->toBe('contact@example.com');
});

it('exposes validation rules and messages from AdminSettingsValidation', function () {
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

it('seeder keys can be assigned to contact_form without conflict', function () {
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

it('legal pages keys can be assigned to front section without conflict', function () {
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

it('recaptcha keys can be assigned to recaptcha section without conflict', function () {
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

it('Setting::normalizeSection returns general for unknown sections', function () {
    expect(Setting::normalizeSection('unknown'))->toBe(Setting::SECTION_GENERAL)
        ->and(Setting::normalizeSection(null))->toBe(Setting::SECTION_GENERAL)
        ->and(Setting::normalizeSection(''))->toBe(Setting::SECTION_GENERAL);
});

// ─────────────────────────────────────────────────────────────────────────────
// T023 — Section guard on setSection
// ─────────────────────────────────────────────────────────────────────────────

it('setSection ignora secciones no presentes en availableSections', function () {
    $user = adminUser();

    $component = Livewire::actingAs($user)->test('admin-settings');
    $originalSection = $component->get('activeSection');

    $component->call('setSection', 'nonexistent_section');

    expect($component->get('activeSection'))->toBe($originalSection);
});

// ─────────────────────────────────────────────────────────────────────────────
// T033 — SC-004: New section tab renders without structural UI changes
// ─────────────────────────────────────────────────────────────────────────────

it('adding data in a new section renders its tab without structural component changes', function () {
    $user = adminUser();
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

it('openTestEmailModal opens modal and resets form', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->assertSet('showTestEmailModal', true)
        ->assertSet('testEmailAddress', '')
        ->assertSet('testEmailStatus', '');
});

it('closeTestEmailModal closes modal and resets state', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->call('closeTestEmailModal')
        ->assertSet('showTestEmailModal', false)
        ->assertSet('testEmailAddress', '')
        ->assertSet('testEmailStatus', '');
});

it('sendTestEmail valida que testEmailAddress sea un email válido', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->set('testEmailAddress', 'not-an-email')
        ->call('sendTestEmail')
        ->assertHasErrors(['testEmailAddress']);
});

it('sendTestEmail requiere SMTP configurado (smtp_host no vacío)', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-settings')
        ->call('openTestEmailModal')
        ->set('testEmailAddress', 'test@example.com')
        ->set('smtpHost', '')
        ->call('sendTestEmail')
        ->assertSet('testEmailStatus', 'error');
});

it('sendTestEmail sends a test email with SMTP configuration', function () {
    $user = adminUser();

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
