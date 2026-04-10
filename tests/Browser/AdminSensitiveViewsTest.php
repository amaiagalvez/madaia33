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
 * - Owner detail (personal data + assignment controls)
 * - Location detail (editable property/validation state)
 */
test('admin can access owner detail sensitive view and sees assignment controls', function () {
    $admin = User::factory()->create();

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
            ->visit('/admin/propietarias/'.$owner->id)
            ->waitFor('[data-assignment-id]', 5)
            ->assertPresent('[data-assignment-id]')
            ->assertPresent('[data-assignment-admin-validated-toggle]')
            ->assertPresent('[data-assignment-owner-validated-toggle]')
            ->assertMissing('[data-action="deactivate-owner"]');
    });
});

test('admin can access location detail sensitive view and sees editable property rows', function () {
    $admin = User::factory()->create();

    $location = Location::factory()->portal()->create();

    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '2B',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $location, $property) {
        $browser->loginAs($admin)
            ->visit('/admin/ubicaciones/'.$location->id)
            ->waitFor('[data-property-id="'.$property->id.'"]', 5)
            ->assertPresent('[data-property-id="'.$property->id.'"]')
            ->assertPresent('[data-assigned]')
            ->assertMissing('[data-admin-validated]')
            ->assertMissing('[data-owner-validated]');
    });
});

test('guest cannot access sensitive owner and location admin views', function () {
    $owner = Owner::factory()->create();
    $location = Location::factory()->create();

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($owner, $location) {
        $browser->visit('/_dusk/logout')
            ->visit('/admin/propietarias/'.$owner->id)
            ->waitForLocation('/eu/pribatua')
            ->assertPathIs('/eu/pribatua')
            ->visit('/admin/ubicaciones/'.$location->id)
            ->waitForLocation('/eu/pribatua')
            ->assertPathIs('/eu/pribatua');
    });
});
