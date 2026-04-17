<?php

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

test('public cookie consent banner can be accepted', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit(route('home.eu'))
            ->waitFor('[data-cookie-consent-banner]', 10)
            ->assertPresent('[data-cookie-consent-banner]')
            ->click('[data-cookie-consent-accept]')
            ->waitUntilMissing('[data-cookie-consent-banner]', 10)
            ->assertScript(<<<'JS'
                (() => document.cookie.includes('madaia_cookie_consent=1'))();
            JS, true);
    });
});
