<?php

/**
 * Validates: Requirements 15.1, 15.2, 15.4
 */

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('legal pages are accessible and rendered in Basque', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/pribatutasun-politika')
            ->assertPathIs('/eu/pribatutasun-politika')
            ->assertSee('Pribatutasun-politika')
            ->visit('/eu/ohar-legala')
            ->assertPathIs('/eu/ohar-legala')
            ->assertSee('Lege-oharra');
    });
});

test('legal pages are rendered in Spanish after switching language', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu')
            ->click('[data-language-option="es"]')
            ->waitForText('Avisos')
            ->visit('/es/politica-de-privacidad')
            ->assertSee('Política de privacidad')
            ->visit('/es/aviso-legal')
            ->assertSee('Aviso legal');
    });
});
