<?php

/**
 * Validates: Requirements 6.1, 6.4
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('admin can create, publish, verify public, unpublish and delete a notice', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();
    $title = 'Dusk Test Iragarkia ' . now()->timestamp;
    $adminNoticesPath = '/admin/iragarkiak';
    $createPublishedAt = '2026-04-10';
    $createPublishedAtDisplay = '10/04/2026';
    $updatedPublishedAt = '2026-04-12';
    $updatedPublishedAtDisplay = '12/04/2026';

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $title, $adminNoticesPath, $createPublishedAt, $createPublishedAtDisplay, $updatedPublishedAt, $updatedPublishedAtDisplay) {
        // Login and go to notices admin
        $browser->loginAs($admin)
            ->visit($adminNoticesPath)
            ->waitForText('Sortu berria');

        // Create notice with explicit published date
        $browser->press('Sortu berria')
            ->waitFor('#titleEu')
            ->type('#titleEu', $title)
            ->type('#contentEu', 'Dusk test edukia.')
            ->waitFor('#publishedAt')
            ->type('#publishedAt', $createPublishedAt);

        $browser->script(<<<'JS'
            const publishButton = Array.from(document.querySelectorAll('[data-admin-field="boolean-toggle"] button'))
                .find((button) => button.textContent.trim() === 'Bai')
                ?? document.querySelector('[data-admin-field="boolean-toggle"] button:first-of-type');
            if (publishButton) {
                publishButton.click();
            }
        JS);

        $browser->waitUntil(
            "(() => { const button = document.querySelector('[data-admin-field=\"boolean-toggle\"] button:first-of-type'); return !!button && button.className.includes('bg-[#d9755b]'); })()",
            5,
        );

        $browser
            ->press('Sortu berria')
            ->waitForText($title)
            ->assertSee($title);

        // Edit notice and update published date
        $browser->script(" 
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                const titleCell = row.querySelector('td:first-child');
                const titleText = titleCell ? titleCell.textContent.replace(/\\s+/g, ' ').trim() : '';

                if (titleText === '{$title}') {
                    const btn = row.querySelector('button[title=\"Editatu\"]');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        $browser->waitFor('#publishedAt', 5)
            ->script(" 
                const input = document.querySelector('#publishedAt');
                if (input) {
                    input.value = '{$updatedPublishedAt}';
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            ");

        $browser->press('Gorde')
            ->waitForTextIn('tbody', $title, 5)
            ->waitUntil(
                "(() => Array.from(document.querySelectorAll('tbody tr')).some((row) => { const titleCell = row.querySelector('td:first-child'); const titleText = titleCell ? titleCell.textContent.replace(/\\s+/g, ' ').trim() : ''; return titleText === '{$title}' && row.textContent.includes('{$updatedPublishedAtDisplay}'); }))()",
                5,
            );

        // Verify visible in public
        $browser->visit('/eu/iragarkiak')
            ->assertSee($title);

        // Unpublish notice from row action
        $browser->visit($adminNoticesPath)
            ->waitForText($title)
            ->pause(500);

        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$title}')) {
                    const btn = row.querySelector('button[title=\"Argitalpena kendu\"]');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        $browser->waitForText('Baieztatu', 5);

        $browser->script(<<<'JS'
            const confirmButton = Array.from(document.querySelectorAll('dialog[open] button'))
                .find((button) => button.textContent.includes('Baieztatu'));
            if (confirmButton) {
                confirmButton.click();
            }
        JS);

        $browser->pause(500);

        // Verify not visible in public
        $browser->visit('/eu/iragarkiak')
            ->assertDontSee($title);

        // Delete — click delete button in the row, then confirm
        $browser->visit($adminNoticesPath)
            ->waitForText($title)
            ->pause(500);

        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$title}')) {
                    const btn = row.querySelector('button[title=\"Ezabatu\"]');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        // Confirm deletion
        $browser->waitForText('Ziur zaude', 5)
            ->press('Ezabatu');

        $browser->pause(1500)
            ->assertDontSee($title);
    });
});
