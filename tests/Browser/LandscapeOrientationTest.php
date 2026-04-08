<?php

/**
 * Validates: Requirements 8.1, 8.2, 8.3, 8.4
 */

use App\Models\Image;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('small iphone landscape keeps header compact and mobile menu scrollable', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->resize(667, 375)
            ->pause(350)
            ->script(<<<'JS'
                const menu = document.querySelector('[data-mobile-menu]');
                const button = document.querySelector('[data-hamburger-button]');
                if (menu && button && getComputedStyle(menu).display !== 'none') {
                    button.click();
                }
            JS);

        $browser->pause(250)
            ->assertScript(
                "return document.querySelector('header').offsetHeight <= Math.floor(window.innerHeight / 3);",
                true
            )
            ->assertScript("return document.querySelector('main') !== null;", true)
            ->click('[data-hamburger-button]')
            ->pause(350)
            ->assertScript("return getComputedStyle(document.querySelector('[data-mobile-menu]')).display !== 'none';", true)
            ->assertScript(
                "return ['auto', 'scroll'].includes(getComputedStyle(document.querySelector('[data-mobile-menu]')).overflowY);",
                true
            )
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-mobile-menu]')).maxHeight !== 'none';",
                true
            );
    });
});

test('gallery and lightbox adapt in landscape breakpoints', function () {
    Image::factory()->count(8)->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/galeria')
            ->resize(667, 375)
            ->pause(350);

        $browser->script(<<<'JS'
            const menu = document.querySelector('[data-mobile-menu]');
            const button = document.querySelector('[data-hamburger-button]');
            if (menu && button && getComputedStyle(menu).display !== 'none') {
                button.click();
            }
        JS);

        $browser->pause(250)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-gallery-grid]')).gridTemplateColumns.split(' ').length;",
                3
            );

        $browser->script("document.querySelector('[data-gallery-open]').click();");

        $browser->pause(350)
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').classList.contains('max-h-[85vh]');",
                true
            )
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').getBoundingClientRect().height <= (window.innerHeight * 0.86);",
                true
            )
            ->resize(1280, 800)
            ->pause(350)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-gallery-grid]')).gridTemplateColumns.split(' ').length;",
                4
            );
    });
});
