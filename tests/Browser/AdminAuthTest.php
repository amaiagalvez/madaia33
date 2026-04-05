<?php

/**
 * Validates: Requirements 6.1, 6.2
 */

use Laravel\Dusk\Browser;

test('admin can login with valid credentials and is redirected to dashboard', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->waitFor('input[name=email]', 5)
            ->type('input[name=email]', 'admin@madaia33.eus')
            ->type('input[name=password]', 'password')
            ->press('[data-test="login-button"]')
            ->waitForLocation('/admin')
            ->assertPathIs('/admin');
    });
});

test('admin login with invalid credentials shows error', function () {
    $this->browse(function (Browser $browser) {
        // Ensure we're logged out before testing invalid login
        $browser->visit('/_dusk/logout')
            ->visit('/login')
            ->waitFor('input[name=email]', 5)
            ->type('input[name=email]', 'admin@madaia33.eus')
            ->type('input[name=password]', 'wrong-password')
            ->press('[data-test="login-button"]')
            ->pause(2000)
            ->assertPathIs('/login');
    });
});
