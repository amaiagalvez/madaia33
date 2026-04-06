<?php

/**
 * Validates: Requirements 15.1, 15.2, 15.4
 */

use Laravel\Dusk\Browser;

test('legal pages are accessible and rendered in Basque', function () {
    /** @var \Tests\DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/politica-de-privacidad')
            ->assertPathIs('/politica-de-privacidad')
            ->assertSee('Pribatutasun-politika')
            ->visit('/aviso-legal')
            ->assertPathIs('/aviso-legal')
            ->assertSee('Lege-oharra');
    });
});

test('legal pages are rendered in Spanish after switching language', function () {
    /** @var \Tests\DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->press('ES')
            ->waitForText('Avisos')
            ->visit('/politica-de-privacidad')
            ->assertSee('Política de privacidad')
            ->visit('/aviso-legal')
            ->assertSee('Aviso legal');
    });
});
