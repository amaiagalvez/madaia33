<?php

/**
 * Validates: Requirements 1.2, 1.3
 */

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('language switcher toggles interface between Spanish and Basque', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertScript("return getComputedStyle(document.querySelector('[data-language-option=\"es\"]')).cursor;", 'pointer')
            ->assertScript("return getComputedStyle(document.querySelector('[data-language-option=\"eu\"]')).cursor;", 'pointer')
            ->assertSee('Iragarkiak') // EU nav text
            ->press('ES')
            ->waitForText('Avisos')
            ->assertSee('Avisos')
            ->assertSee('Galería')
            ->press('EU')
            ->waitForText('Iragarkiak')
            ->assertSee('Iragarkiak');
    });
});
