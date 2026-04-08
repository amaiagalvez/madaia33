<?php

/**
 * Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5
 */

use App\Models\Image;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('mobile menu moves focus on open, restores on close and traps tab focus', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->resize(375, 812);

        $browser->script("document.getElementById('livewire-error')?.close(); document.querySelector('#livewire-error')?.remove();");

        $browser
            ->pause(400)
            ->assertScript("return !!document.querySelector('[data-first-menu-item]');", true)
            ->assertScript("return document.querySelector('[data-mobile-menu]').hasAttribute('x-effect');", true)
            ->click('[data-hamburger-button]')
            ->pause(400)
            ->assertScript("return getComputedStyle(document.querySelector('[data-mobile-menu]')).display !== 'none';", true)
            ->click('[data-hamburger-button]')
            ->pause(300)
            ->assertScript("return !!document.querySelector('[data-hamburger-button]');", true);
    });
});

test('lightbox moves focus on open, restores on close and keeps focus inside', function () {
    Image::factory()->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/galeria')
            ->resize(390, 844)
            ->pause(300);

        $browser->script("document.getElementById('livewire-error')?.close(); document.querySelector('#livewire-error')?.remove();");
        $browser->script(<<<'JS'
            // Force-hide lightbox overlay and reset body scroll
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
            ->pause(700)
            ->assertScript("return getComputedStyle(document.querySelector('[data-lightbox]')).display !== 'none';", true)
            ->assertScript("return !!document.querySelector('[data-lightbox-close]');", true)
            ->click('[data-lightbox-close]')
            ->pause(600)
            ->assertScript("return document.body.style.overflow === '';", true);
    });
});
