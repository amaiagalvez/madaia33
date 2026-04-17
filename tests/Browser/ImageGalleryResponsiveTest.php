<?php

/**
 * Validates: Requirements 5.1, 5.2, 5.3, 5.4, 8.2, 11.1
 */

use App\Models\Image;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

const GALLERY_PATH = '/eu/argazki-bilduma';

test('gallery grid responds to mobile tablet and desktop breakpoints', function () {
    Image::factory()->count(8)->create();
    $gridColumnsScript = "return getComputedStyle(document.querySelector('[data-gallery-grid]')).gridTemplateColumns.split(' ').length;";

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($gridColumnsScript) {
        $browser->visit(GALLERY_PATH);

        $browser->resize(375, 812)
            ->pause(300)
            ->assertScript($gridColumnsScript, 2);

        $browser->resize(768, 1024)
            ->pause(300)
            ->assertScript($gridColumnsScript, 3);

        $browser->resize(1200, 900)
            ->pause(300)
            ->assertScript($gridColumnsScript, 4);
    });
});

test('lightbox adapts orientation and closes with escape and outside click', function () {
    Image::factory()->create();
    $galleryOpenSelector = '[data-gallery-open]';
    $overflowScript = 'return document.body.style.overflow;';

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($galleryOpenSelector, $overflowScript) {
        $browser->visit(GALLERY_PATH)
            ->dismissCookieConsentBanner()
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
            ->click($galleryOpenSelector)
            ->pause(300)
            ->assertScript("return document.body.style.overflow === 'hidden';", true)
            ->assertScript("return getComputedStyle(document.querySelector('[data-lightbox]')).display !== 'none';", true)
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').classList.contains('max-h-[90vh]');",
                true
            )
            ->assertScript($overflowScript, 'hidden')
            ->script("document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));");

        $browser->pause(300)
            ->assertScript($overflowScript, '');

        $browser
            ->resize(1024, 640)
            ->click($galleryOpenSelector)
            ->pause(300)
            ->assertScript(
                "return document.querySelector('[data-lightbox] img').classList.contains('max-h-[85vh]');",
                true
            );

        $browser->script("document.querySelector('[data-lightbox]').dispatchEvent(new MouseEvent('click', { bubbles: true }));");

        $browser
            ->pause(300)
            ->assertScript($overflowScript, '');
    });
});

test('lightbox still opens for captions with quotes and line breaks', function () {
    Image::factory()->create([
        'alt_text_eu' => "Auzoaren \"argazkia\"\n2026ko oroitzapena",
        'alt_text_es' => "La \"foto\" del barrio\nrecuerdo de 2026",
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit(GALLERY_PATH)
            ->dismissCookieConsentBanner()
            ->pause(350)
            ->click('[data-gallery-open]')
            ->pause(350)
            ->assertScript("return document.body.style.overflow === 'hidden';", true)
            ->assertScript("return getComputedStyle(document.querySelector('[data-lightbox]')).display !== 'none';", true)
            ->assertScript("return !!document.querySelector('[data-lightbox] img');", true);
    });
});

test('lightbox closes with swipe down gesture', function () {
    Image::factory()->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit(GALLERY_PATH)
            ->dismissCookieConsentBanner()
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
            ->assertScript("return document.body.style.overflow === 'hidden';", true)
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
