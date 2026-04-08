<?php

/**
 * Validates: Requirements 16.4
 */

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('sitemap.xml is publicly accessible and contains public URLs', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/sitemap.xml');

        $source = $browser->driver->getPageSource();

        expect($source)->toContain('<urlset')
            ->and($source)->toContain('<loc>');

        // Should contain public routes
        expect($source)->toContain('/eu/iragarkiak')
            ->and($source)->toContain('/es/avisos')
            ->and($source)->toContain('/eu/argazki-bilduma')
            ->and($source)->toContain('/eu/harremana');
    });
});
