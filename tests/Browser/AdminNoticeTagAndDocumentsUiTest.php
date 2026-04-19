<?php

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('admin notices form exposes tag and document controls', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin/iragarkiak')
            ->waitForText('Sortu berria')
            ->press('Sortu berria')
            ->waitFor('[data-notice-tag-select]')
            ->assertPresent('[data-notice-tag-select]')
            ->assertPresent('[data-notice-documents-input]');
    });
});
