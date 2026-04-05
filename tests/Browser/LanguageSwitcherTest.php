<?php

/**
 * Validates: Requirements 1.2, 1.3
 */

use Laravel\Dusk\Browser;

test('language switcher toggles interface between Spanish and Basque', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
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
