<?php

/**
 * Validates: Requirements 10.5
 */

use App\Models\Setting;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('contact form complete flow with recaptcha disabled', function () {
    Setting::updateOrCreate(['key' => 'recaptcha_secret_key'], ['value' => '']);
    Setting::updateOrCreate(['key' => 'recaptcha_site_key'], ['value' => '']);
    Setting::updateOrCreate(['key' => 'legal_checkbox_text_eu'], ['value' => '<p>Kontakturako lege testua</p>']);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/harremana')
            ->assertSee('Kontaktua')
            ->assertSee('Pribatutasun politika irakurri eta onartzen dut.')
            ->click('[data-test="contact-legal-modal-trigger"]')
            ->waitFor('[data-test="contact-legal-modal"]', 5)
            ->assertSee('Kontakturako lege testua')
            ->keys('', '{escape}')
            ->check('#contact-legal')
            ->type('#contact-name', 'Ane Etxebarria')
            ->type('#contact-email', 'ane@example.com')
            ->type('#contact-subject', 'Proba gaia')
            ->type('#contact-message', 'Hau proba mezu bat da.')
            ->script("document.getElementById('recaptcha-token').value = 'test-token';
                        document.getElementById('recaptcha-token').dispatchEvent(new Event('input'));");

        $browser->press('Bidali')
            ->waitForText('Zure mezua bidali da', 10)
            ->assertSee('Zure mezua bidali da');
    });
});

test('contact form ignores a rapid double click on submit', function () {
    Setting::updateOrCreate(['key' => 'recaptcha_secret_key'], ['value' => '']);
    Setting::updateOrCreate(['key' => 'recaptcha_site_key'], ['value' => '']);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/harremana')
            ->assertSee('Kontaktua')
            ->type('#contact-name', 'Ane Etxebarria')
            ->type('#contact-email', 'ane@example.com')
            ->type('#contact-subject', 'Klik bikoitza')
            ->type('#contact-message', 'Mezu bakarra bidali behar da.')
            ->check('#contact-legal')
            ->script("document.getElementById('recaptcha-token').value = 'test-token';
                        document.getElementById('recaptcha-token').dispatchEvent(new Event('input'));\n
                        const button = document.querySelector('[data-contact-submit]');
                        button.click();
                        button.click();");

        $browser->waitUsing(5, 100, fn() => (bool) $browser->script("return document.querySelector('[data-contact-submit]').disabled;")[0])
            ->assertScript("return document.querySelector('[data-contact-submit]').disabled === true;")
            ->assertScript("return document.querySelectorAll('[role=\"alert\"]').length <= 1;")
            ->waitForText('Zure mezua bidali da', 10)
            ->assertSee('Zure mezua bidali da');
    });
});

test('contact form shows validation errors when fields are empty', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/harremana')
            ->press('Bidali')
            ->waitForText('beharrezkoa', 5)
            ->assertSee('beharrezkoa');
    });
});
