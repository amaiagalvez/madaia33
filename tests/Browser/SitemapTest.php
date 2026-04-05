<?php

/**
 * Validates: Requirements 16.4
 */

use Laravel\Dusk\Browser;

test('sitemap.xml is publicly accessible and contains public URLs', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/sitemap.xml');

        $source = $browser->driver->getPageSource();

        expect($source)->toContain('<urlset')
            ->and($source)->toContain('<loc>');

        // Should contain public routes
        expect($source)->toContain('/avisos')
            ->and($source)->toContain('/galeria')
            ->and($source)->toContain('/contacto');
    });
});
