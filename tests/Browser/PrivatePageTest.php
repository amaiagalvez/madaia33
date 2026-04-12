<?php

/**
 * Validates: Requirements 5.2, 5.3
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('private page shows placeholder with login link for unauthenticated visitors', function () {
    $privatePath = '/eu/pribatua';

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($privatePath) {
        $browser->visit($privatePath)
            ->assertPathIs($privatePath)
            ->assertPresent('[data-private-placeholder]')
            ->assertSee('Eremu pribatuan sartu')
            ->assertSee('Saioa hasi');
    });
});

test('private page redirects authenticated admin to dashboard', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();
    $privatePath = '/eu/pribatua';

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $privatePath) {
        $browser->loginAs($admin)
            ->visit($privatePath)
            ->waitForLocation('/admin')
            ->assertPathIs('/admin');
    });
});
