<?php

use App\Models\Owner;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('profile contact modal opens centered and closes with escape', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner) {
        $browser->loginAs($owner->user)
            ->visit('/eu/profila')
            ->waitFor('[data-profile-panel="overview"]', 5)
            ->assertMissing('[data-profile-terms-modal]')
            ->assertPresent('[data-test="profile-contact-modal-trigger"]')
            ->script("const trigger = document.querySelector('[data-test=\"profile-contact-modal-trigger\"]'); if (trigger) { trigger.scrollIntoView({block: 'center'}); trigger.click(); }");

        $browser->waitFor('[data-test="profile-contact-modal-dialog"]', 10)
            ->assertPresent('[data-test="profile-contact-modal-message"]')
            ->assertScript(
                '(() => {'
                    . 'var dialog = document.querySelector("[data-test=\"profile-contact-modal-dialog\"]");'
                    . 'if (!dialog) return false;'
                    . 'var panel = dialog.querySelector(".max-w-lg");'
                    . 'if (!panel) return false;'
                    . 'var rect = panel.getBoundingClientRect();'
                    . 'var centerX = window.innerWidth / 2;'
                    . 'return Math.abs(((rect.left + rect.right) / 2) - centerX) < 48;'
                    . '})()',
                true,
            );

        $browser->script("window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));");

        $browser->pause(400)
            ->assertScript(
                '(() => {'
                    . 'var dialog = document.querySelector("[data-test=\"profile-contact-modal-dialog\"]");'
                    . 'if (!dialog) return true;'
                    . 'var style = window.getComputedStyle(dialog);'
                    . 'return style.display === "none" || style.visibility === "hidden" || dialog.getAttribute("x-cloak") !== null;'
                    . '})()',
                true,
            )
            ->assertScript(
                '(() => {'
                    . 'var trigger = document.querySelector("[data-test=\"profile-contact-modal-trigger\"]");'
                    . 'return trigger !== null && document.activeElement === trigger;'
                    . '})()',
                true,
            );
    });
});
