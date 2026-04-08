<?php

/**
 * Validates: Requirements 6.1, 6.4
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('admin can create, publish, verify public, unpublish and delete a notice', function () {
    $admin = User::where('email', 'admin@madaia33.eus')->firstOrFail();
    $title = 'Dusk Test Iragarkia '.now()->timestamp;

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $title) {
        // Login and go to notices admin
        $browser->loginAs($admin)
            ->visit('/admin/avisos')
            ->assertSee('Iragarkiak');

        // Create notice (draft, not public)
        $browser->press('Sortu')
            ->waitFor('#titleEu')
            ->type('#titleEu', $title)
            ->type('#contentEu', 'Dusk test edukia.')
            ->press('Gorde')
            ->waitForText($title)
            ->assertSee($title);

        // Publish notice — click the "Argitaratu" button in the row containing the title
        $browser->waitForText('Argitaratu')
            ->pause(500);

        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$title}')) {
                    const btn = row.querySelector('button.text-green-600');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        $browser->waitForText('Argitalpena kendu', 5);

        // Verify visible in public
        $browser->visit('/avisos')
            ->assertSee($title);

        // Unpublish
        $browser->visit('/admin/avisos')
            ->waitForText($title)
            ->pause(500);

        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$title}')) {
                    const btn = row.querySelector('button.text-yellow-600');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        $browser->pause(1000);

        // Verify not visible in public
        $browser->visit('/avisos')
            ->assertDontSee($title);

        // Delete — click delete button in the row, then confirm
        $browser->visit('/admin/avisos')
            ->waitForText($title)
            ->pause(500);

        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$title}')) {
                    const btns = row.querySelectorAll('button.text-red-600');
                    if (btns.length) btns[btns.length - 1].click();
                    break;
                }
            }
        ");

        // Confirm deletion
        $browser->waitForText('Ziur zaude', 5)
            ->pause(300);

        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$title}')) {
                    const btns = row.querySelectorAll('button.text-red-600');
                    if (btns.length) btns[btns.length - 1].click();
                    break;
                }
            }
        ");

        $browser->pause(1500)
            ->assertDontSee($title);
    });
});
