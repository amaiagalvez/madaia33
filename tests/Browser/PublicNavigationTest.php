<?php

/**
 * Validates: Requirements 2.1, 3.1, 5.2, 5.3
 */

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('public navigation reaches notices, gallery and contact pages', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->clickLink('Iragarkiak')
            ->assertPathIs('/avisos')
            ->assertSee('Iragarkiak')
            ->visit('/')
            ->clickLink('Argazki-bilduma')
            ->assertPathIs('/galeria')
            ->assertSee('Argazki-bilduma')
            ->visit('/')
            ->clickLink('Kontaktua')
            ->assertPathIs('/contacto')
            ->assertSee('Kontaktua');
    });
});
