<?php

/**
 * Validates: Requirements 2.1, 3.1, 5.2, 5.3
 */

use App\Models\User;
use App\Models\Notice;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('public navigation reaches notices, gallery and contact pages', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu')
            ->clickLink('Iragarkiak')
            ->assertPathIs('/eu/iragarkiak')
            ->assertSee('Iragarkiak')
            ->visit('/eu')
            ->clickLink('Argazki-bilduma')
            ->assertPathIs('/eu/argazki-bilduma')
            ->assertSee('Argazki-bilduma')
            ->visit('/eu')
            ->clickLink('Kontaktua')
            ->assertPathIs('/eu/harremana')
            ->assertSee('Kontaktua');
    });
});

test('authenticated users see their name and logout button in front header', function () {
    $user = User::factory()->create([
        'name' => 'Header User',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/eu')
            ->assertSee('Header User')
            ->assertPresent('[data-test="logout-button"]');
    });
});

test('public header language links expose matching accessible names and notice dates use high-contrast token', function () {
    Notice::factory()->public()->create([
        'published_at' => now(),
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/es')
            ->waitFor('[data-language-option="es"]', 5)
            ->assertScript(
                '(() => {'
                    . 'var links = Array.from(document.querySelectorAll("[data-language-option]"));'
                    . 'if (links.length === 0) return false;'
                    . 'return links.every(function (link) {'
                    . 'var aria = (link.getAttribute("aria-label") || "").trim();'
                    . 'var text = (link.textContent || "").trim();'
                    . 'return aria.length > 0 && aria === text;'
                    . '});'
                    . '})()',
                true,
            )
            ->visit('/es/avisos')
            ->waitFor('[data-notice-published-at]', 5)
            ->assertScript(
                '(() => {'
                    . 'var dates = Array.from(document.querySelectorAll("[data-notice-published-at]"));'
                    . 'if (dates.length === 0) return false;'
                    . 'return dates.every(function (node) {'
                    . 'return node.classList.contains("text-[#793d3d]");'
                    . '});'
                    . '})()',
                true,
            );
    });
});
