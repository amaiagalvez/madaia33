<?php

/**
 * Validates: Requirements 15.1, 15.2
 */

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('footer shows legal links with correct urls and labels in Basque', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu')
            ->assertPresent('footer a[href*="pribatutasun-politika"]')
            ->assertPresent('footer a[href*="ohar-legala"]')
            ->assertSeeIn('footer', 'Pribatutasun-politika')
            ->assertSeeIn('footer', 'Lege-oharra');
    });
});
