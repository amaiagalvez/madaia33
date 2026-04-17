<?php

/**
 * Validates: Requirements 12.4, 13.4
 */

use App\Models\User;
use App\Models\Setting;
use Tests\DuskTestCase;
use App\SupportedLocales;
use Laravel\Dusk\Browser;

test('bilingual settings blocks keep the same height when switching between EUS and CAS', function () {
    $basqueLocale = SupportedLocales::BASQUE;
    $spanishLocale = SupportedLocales::SPANISH;
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    Setting::query()->updateOrCreate(
        ['key' => 'legal_page_privacy_policy_eu'],
        ['value' => '<p>Pribatutasun politikaren edukia hemen agertuko da.</p>', 'section' => Setting::SECTION_FRONT],
    );
    Setting::query()->updateOrCreate(
        ['key' => 'legal_page_privacy_policy_es'],
        ['value' => '<p>El contenido de la política de privacidad aparecerá aquí.</p>', 'section' => Setting::SECTION_FRONT],
    );
    Setting::query()->updateOrCreate(
        ['key' => 'legal_checkbox_text_eu'],
        ['value' => '<p>Pribatutasun-politika onartzen dut.</p>', 'section' => Setting::SECTION_CONTACT_FORM],
    );
    Setting::query()->updateOrCreate(
        ['key' => 'legal_checkbox_text_es'],
        ['value' => '<p>Acepto la política de privacidad.</p>', 'section' => Setting::SECTION_CONTACT_FORM],
    );
    Setting::query()->updateOrCreate(
        ['key' => 'admin_email'],
        ['value' => 'info@madaia33.eus', 'section' => Setting::SECTION_CONTACT_FORM],
    );

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $basqueLocale, $spanishLocale) {
        $browser->loginAs($admin)
            ->visit('/admin/konfigurazioa')
            ->waitFor('[data-settings-section="front"]', 5)
            ->click('[data-settings-section="front"]')
            ->waitFor('[data-bilingual-field="privacyContentEu"]', 5)
            ->assertScript(<<<JS
                (() => {
                    const root = document.querySelector('[data-bilingual-field="privacyContentEu"]');
                    if (!root) {
                        return false;
                    }

                root.querySelector('[data-bilingual-tab="{$basqueLocale}"]').click();
                    const euHeight = Math.round(root.getBoundingClientRect().height);

                root.querySelector('[data-bilingual-tab="{$spanishLocale}"]').click();
                    const esHeight = Math.round(root.getBoundingClientRect().height);

                    return Math.abs(euHeight - esHeight) <= 1;
                })();
            JS, true)
            ->click('[data-settings-section="contact_form"]')
            ->waitFor('[data-bilingual-field="legalCheckboxTextEu"]', 5)
            ->assertScript(<<<JS
                (() => {
                    const root = document.querySelector('[data-bilingual-field="legalCheckboxTextEu"]');
                    if (!root) {
                        return false;
                    }

                root.querySelector('[data-bilingual-tab="{$basqueLocale}"]').click();
                    const euHeight = Math.round(root.getBoundingClientRect().height);

                root.querySelector('[data-bilingual-tab="{$spanishLocale}"]').click();
                    const esHeight = Math.round(root.getBoundingClientRect().height);

                    return Math.abs(euHeight - esHeight) <= 1;
                })();
            JS, true);
    });
});
