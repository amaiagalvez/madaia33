<?php

/**
 * Validates: Requirements 5.2, 5.3
 */

use App\Models\User;
use Laravel\Dusk\Browser;

test('private page shows placeholder with login link for unauthenticated visitors', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/privado')
            ->assertPathIs('/privado')
            ->assertPresent('a[href*="login"]');
    });
});

test('private page shows development message for authenticated admin', function () {
    $admin = User::where('email', 'admin@madaia33.eus')->firstOrFail();

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/privado')
            ->assertPathIs('/privado')
            ->assertSee('garatzen');
    });
});
