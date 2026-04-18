<?php

use App\Models\User;
use App\Models\Owner;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

test('admin dashboard shows invalid contacts owners stat card', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    Owner::factory()->create([
        'coprop1_email_invalid' => true,
    ]);

    Owner::factory()->create([
        'coprop1_phone_invalid' => true,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit(route('admin.dashboard'))
            ->waitFor('[data-admin-stat-invalid-contacts]', 10)
            ->assertPresent('[data-admin-stat-invalid-contacts]')
            ->assertSeeIn('[data-admin-stat-invalid-contacts]', '2');
    });
});
