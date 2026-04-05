<?php

/**
 * Validates: Requirements 5.1, 5.2, 5.3, 5.4, 8.2, 11.1
 */

use App\Models\Image;
use Laravel\Dusk\Browser;

test('gallery grid responds to mobile tablet and desktop breakpoints', function () {
    Image::factory()->count(8)->create();

    $this->browse(function (Browser $browser) {
        $browser->visit('/galeria');

        $browser->resize(375, 812)
            ->pause(300)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-gallery-grid]')).gridTemplateColumns.split(' ').length;",
                2
            );

        $browser->resize(768, 1024)
            ->pause(300)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-gallery-grid]')).gridTemplateColumns.split(' ').length;",
                3
            );

        $browser->resize(1200, 900)
            ->pause(300)
            ->assertScript(
                "return getComputedStyle(document.querySelector('[data-gallery-grid]')).gridTemplateColumns.split(' ').length;",
                4
            );
    });
});

test('lightbox adapts orientation and closes with escape and outside click', function () {
    Image::factory()->create();

    $this->browse(function (Browser $browser) {
        $browser->visit('/galeria')
            ->resize(390, 844)
            ->pause(350);

        $browser->script(<<<'JS'
            const overlay = document.querySelector('[data-lightbox]');
            if (overlay) {
                overlay.style.display = 'none';
                try {
                    const root = overlay.closest('[x-data]');
                    if (root && window.Alpine) {
                        const data = Alpine.$data(root);
                        if (data) { data.open = false; data.touchStartY = null; }
                    }
                } catch(e) {}
            }
            document.body.style.overflow = '';
        JS);

        $browser
            ->pause(300)
            ->click('[data-gallery-open]')
            ->pause(300)
            ->assertScript("return getComputedStyle(document.querySelector('[data-lightbox]')).display !== 'none';", true)
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').classList.contains('max-h-[90vh]');",
                true
            )
            ->assertScript(
                "return document.querySelector('[data-lightbox-close]').offsetHeight >= 44 && document.querySelector('[data-lightbox-close]').offsetWidth >= 44;",
                true
            )
            ->assertScript('return document.body.style.overflow;', 'hidden')
            ->script("document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));");

        $browser->pause(300)
            ->assertScript('return document.body.style.overflow;', '');

        $browser
            ->resize(1024, 640)
            ->click('[data-gallery-open]')
            ->pause(300)
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').classList.contains('max-h-[85vh]');",
                true
            );

        $browser->script("document.querySelector('[data-lightbox]').dispatchEvent(new MouseEvent('click', { bubbles: true }));");

        $browser
            ->pause(300)
            ->assertScript('return document.body.style.overflow;', '');
    });
});

test('lightbox closes with swipe down gesture', function () {
    Image::factory()->create();

    $this->browse(function (Browser $browser) {
        $browser->visit('/galeria')
            ->resize(390, 844)
            ->pause(350);

        $browser->script(<<<'JS'
            const overlay = document.querySelector('[data-lightbox]');
            if (overlay) {
                overlay.style.display = 'none';
                try {
                    const root = overlay.closest('[x-data]');
                    if (root && window.Alpine) {
                        const data = Alpine.$data(root);
                        if (data) { data.open = false; data.touchStartY = null; }
                    }
                } catch(e) {}
            }
            document.body.style.overflow = '';
        JS);

        $browser
            ->pause(300)
            ->click('[data-gallery-open]')
            ->pause(300)
            ->assertScript("return getComputedStyle(document.querySelector('[data-lightbox]')).display !== 'none';", true)
            ->script(<<<'JS'
                var lightbox = document.querySelector('[data-lightbox]');
                var startEvent = new Event('touchstart', { bubbles: true, cancelable: true });
                Object.defineProperty(startEvent, 'touches', { value: [{ clientY: 100 }] });
                lightbox.dispatchEvent(startEvent);

                var moveEvent = new Event('touchmove', { bubbles: true, cancelable: true });
                Object.defineProperty(moveEvent, 'touches', { value: [{ clientY: 220 }] });
                lightbox.dispatchEvent(moveEvent);
            JS);

        $browser->pause(400)
            ->assertScript('return document.body.style.overflow;', '');
    });
});
