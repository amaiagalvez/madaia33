<?php

use App\Models\User;
use App\Models\Owner;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Livewire\Admin\Owners;
use App\Livewire\Admin\Locations;
use App\Models\PropertyAssignment;
use App\Livewire\Admin\LocationDetail;

it('renders admin locations list for selected type', function () {
    $user = User::factory()->create();

    Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    Location::factory()->create(['type' => 'garage', 'code' => 'P-1', 'name' => 'Garaje P-1']);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSee('Portal 33-A')
        ->assertDontSee('Garaje P-1')
        ->call('setType', 'garage')
        ->assertSee('Garaje P-1')
        ->assertDontSee('Portal 33-A');
});

it('renders location detail with assignment badges', function () {
    $user = User::factory()->create();

    $location = Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    $property = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
    ]);

    $owner = Owner::factory()->create();

    PropertyAssignment::factory()->create([
        'property_id' => $property->id,
        'owner_id' => $owner->id,
        'end_date' => null,
        'admin_validated' => true,
        'owner_validated' => true,
    ]);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $location])
        ->assertSee('1A')
        ->assertSeeHtml('data-assigned="yes"')
        ->assertSeeHtml('data-admin-validated="yes"')
        ->assertSeeHtml('data-owner-validated="yes"');
});

it('filters owners by active portal assignment', function () {
    $user = User::factory()->create();

    $portalA = Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    $portalB = Location::factory()->create(['type' => 'portal', 'code' => '33-B', 'name' => 'Portal 33-B']);

    $propertyA = Property::factory()->create(['location_id' => $portalA->id, 'name' => '1A']);
    $propertyB = Property::factory()->create(['location_id' => $portalB->id, 'name' => '2B']);

    $ownerA = Owner::factory()->create(['coprop1_name' => 'Ane A']);
    $ownerB = Owner::factory()->create(['coprop1_name' => 'Bea B']);

    PropertyAssignment::factory()->create([
        'property_id' => $propertyA->id,
        'owner_id' => $ownerA->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'property_id' => $propertyB->id,
        'owner_id' => $ownerB->id,
        'end_date' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('filterPortal', (string) $portalA->id)
        ->assertSee('Ane A')
        ->assertDontSee('Bea B');
});

it('renders new admin pages for locations and owners', function () {
    $user = User::factory()->create();

    $location = Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    $owner = Owner::factory()->create();

    test()->actingAs($user)
        ->get(route('admin.locations.portals'))
        ->assertOk();

    test()->actingAs($user)
        ->get(route('admin.locations.show', $location))
        ->assertOk();

    test()->actingAs($user)
        ->get(route('admin.owners.index'))
        ->assertOk();

    test()->actingAs($user)
        ->get(route('admin.owners.show', $owner))
        ->assertOk();
});
