<?php

/**
 * Validates: Requirements 2.1, 3.1, 5.2, 5.3
 */

use App\Models\User;
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
            ->assertSee('Saioa itxi');
    });
});
