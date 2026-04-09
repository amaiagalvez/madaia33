<?php

/**
 * Validates: Requirements 5.2, 5.3
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('private page shows placeholder with login link for unauthenticated visitors', function () {
    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) {
        $browser->visit('/eu/pribatua')
            ->assertPathIs('/eu/pribatua')
            ->assertPresent('a[href*="login"]');
    });
});

test('private page shows development message for authenticated admin', function () {
    $admin = User::where('email', 'admin@madaia33.eus')->firstOrFail();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/eu/pribatua')
            ->assertPathIs('/eu/pribatua')
            ->assertSee('garatzen');
    });
});
