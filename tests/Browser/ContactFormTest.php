<?php

/**
 * Validates: Requirements 10.5
 */

use App\Models\Setting;
use Laravel\Dusk\Browser;

test('contact form complete flow with recaptcha disabled', function () {
    Setting::updateOrCreate(['key' => 'recaptcha_secret_key'], ['value' => '']);
    Setting::updateOrCreate(['key' => 'recaptcha_site_key'], ['value' => '']);

    $this->browse(function (Browser $browser) {
        $browser->visit('/contacto')
            ->assertSee('Kontaktua')
            ->type('#contact-name', 'Ane Etxebarria')
            ->type('#contact-email', 'ane@example.com')
            ->type('#contact-subject', 'Proba gaia')
            ->type('#contact-message', 'Hau proba mezu bat da.')
            ->check('#contact-legal')
            ->script("document.getElementById('recaptcha-token').value = 'test-token';
                      document.getElementById('recaptcha-token').dispatchEvent(new Event('input'));");

        $browser->press('Bidali')
            ->waitForText('Zure mezua bidali da', 10)
            ->assertSee('Zure mezua bidali da');
    });
});

test('contact form shows validation errors when fields are empty', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/contacto')
            ->press('Bidali')
            ->waitForText('beharrezkoa', 5)
            ->assertSee('beharrezkoa');
    });
});
