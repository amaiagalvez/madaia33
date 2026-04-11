<?php

use App\Models\User;
use App\Models\Owner;
use Tests\DuskTestCase;
use App\Models\Location;
use App\Models\Property;
use Laravel\Dusk\Browser;
use App\Models\PropertyAssignment;

/**
 * Validates sensitive admin views:
 * - Owner index inline panel (assignment controls)
 * - Location detail (editable property/validation state)
 */
test('admin can access owner sensitive inline controls from index', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Owner',
    ]);

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $owner) {
        $browser->loginAs($admin)
            ->visit('/admin/propietarias')
            ->waitFor('[data-owner-id="' . $owner->id . '"]', 5)
            ->assertPresent('[data-owner-id="' . $owner->id . '"]')
            ->click('[data-action="toggle-owner-inline-' . $owner->id . '"]')
            ->waitFor('[data-owner-inline-panel="' . $owner->id . '"]', 5)
            ->assertPresent('[data-owner-inline-panel="' . $owner->id . '"]')
            ->assertPresent('[data-owner-inline-create="' . $owner->id . '"]')
            ->assertMissing('[data-action="deactivate-owner"]');
    });
});

test('admin top user menu dropdown opens from the desktop header', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin')
            ->waitFor('[data-test="sidebar-menu-button"]', 5)
            ->click('[data-test="sidebar-menu-button"]')
            ->waitFor('[data-test="logout-button"]', 5)
            ->assertPresent('[data-test="logout-button"]')
            ->assertSee($admin->email);
    });
});

test('admin can access location detail sensitive view and sees editable property rows', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '2B',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $location, $property) {
        $browser->loginAs($admin)
            ->visit('/admin/ubicaciones/' . $location->id)
            ->waitFor('[data-property-id="' . $property->id . '"]', 5)
            ->assertPresent('[data-property-id="' . $property->id . '"]')
            ->assertPresent('[data-assigned]')
            ->assertMissing('[data-admin-validated]')
            ->assertMissing('[data-owner-validated]');
    });
});

test('guest cannot access sensitive owner and location admin views', function () {
    $location = Location::factory()->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($location) {
        $browser->visit('/_dusk/logout')
            ->visit('/admin/propietarias')
            ->waitFor('[data-test="login-button"]', 10)
            ->assertPresent('[data-test="login-button"]')
            ->assertPathBeginsWith('/eu/pribatua')
            ->visit('/admin/ubicaciones/' . $location->id)
            ->waitFor('[data-test="login-button"]', 10)
            ->assertPresent('[data-test="login-button"]')
            ->assertPathBeginsWith('/eu/pribatua');
    });
});
