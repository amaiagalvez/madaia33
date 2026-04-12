<?php

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('forgot password page uses the branded auth shell', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/pasahitza-ahaztu')
            ->waitFor('[data-auth-shell]', 5)
            ->assertPresent('[data-auth-shell] img[src$="madaia33.png"]')
            ->assertPresent('input[name=email]')
            ->assertPresent('[data-test="email-password-reset-link-button"]')
            ->assertSee('Pasahitza berreskuratu');
    });
});

test('reset password page keeps the branded auth shell and project actions', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/es/restablecer-contrasena/token-de-prueba?email=test@example.com')
            ->waitFor('[data-auth-shell]', 5)
            ->assertPresent('[data-auth-shell] img[src$="madaia33.png"]')
            ->assertInputValue('email', 'test@example.com')
            ->assertPresent('input[name=password]')
            ->assertPresent('input[name=password_confirmation]')
            ->assertPresent('[data-test="reset-password-button"]')
            ->assertSee('Restablecer contraseña');
    });
});
