<?php

use App\Models\User;
use App\Models\Owner;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Livewire\Admin\Owners;
use App\Mail\OwnerWelcomeMail;
use App\Livewire\Admin\Locations;
use App\Models\PropertyAssignment;
use App\Livewire\Admin\OwnerDetail;
use Illuminate\Support\Facades\Mail;
use App\Livewire\Admin\LocationDetail;

it('renders admin locations list for selected type', function () {
    $user = adminUser();

    Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    Location::factory()->create(['type' => 'local', 'code' => 'L-1', 'name' => 'Local L-1']);
    Location::factory()->create(['type' => 'garage', 'code' => 'P-1', 'name' => 'Garaje P-1']);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSee('Portal 33-A')
        ->assertDontSee('Local L-1')
        ->assertDontSee('Garaje P-1')
        ->call('setType', 'local')
        ->assertSee('Local L-1')
        ->assertDontSee('Portal 33-A')
        ->call('setType', 'garage')
        ->assertSee('Garaje P-1')
        ->assertDontSee('Portal 33-A');
});

it('renders location detail with assignment badges', function () {
    $user = adminUser();

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
        ->assertSeeHtml('data-assigned="yes"');

    test()->actingAs($user)
        ->get(route('admin.locations.show', $location))
        ->assertOk()
        ->assertDontSee('data-admin-validated=', false)
        ->assertDontSee('data-owner-validated=', false);
});

it('toggles assignment validations one by one and blocks closed assignments', function () {
    $user = adminUser();

    $owner = Owner::factory()->create();
    $location = Location::factory()->portal()->create(['code' => '33-A']);

    $activeProperty = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
    ]);

    $closedProperty = Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1B',
    ]);

    $activeAssignment = PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $activeProperty->id,
        'end_date' => null,
        'admin_validated' => false,
        'owner_validated' => false,
    ]);

    $closedAssignment = PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $closedProperty->id,
        'end_date' => now()->subDay(),
        'admin_validated' => false,
        'owner_validated' => false,
    ]);

    Livewire::actingAs($user)
        ->test(OwnerDetail::class, ['owner' => $owner])
        ->call('toggleAssignmentValidation', $activeAssignment->id, 'admin_validated')
        ->call('toggleAssignmentValidation', $activeAssignment->id, 'owner_validated')
        ->call('toggleAssignmentValidation', $closedAssignment->id, 'admin_validated');

    expect($activeAssignment->refresh()->admin_validated)->toBeTrue()
        ->and($activeAssignment->owner_validated)->toBeTrue()
        ->and($closedAssignment->refresh()->admin_validated)->toBeFalse();
});

it('accepts comma decimals for location percentages and stores them normalized with dot', function () {
    $user = adminUser();

    $location = Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);

    $component = Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $location])
        ->set('newPropertyName', '7C')
        ->set('newCommunityPct', '1,2500')
        ->set('newLocationPct', '2,5000')
        ->call('addProperty');

    $createdProperty = Property::query()
        ->where('location_id', $location->id)
        ->where('name', '7C')
        ->first();

    expect($createdProperty)->not->toBeNull()
        ->and((float) $createdProperty->community_pct)->toBe(1.25)
        ->and((float) $createdProperty->location_pct)->toBe(2.5);

    $component
        ->call('startEditing', $createdProperty->id)
        ->set('editName', '7D')
        ->set('editCommunityPct', '3,5000')
        ->set('editLocationPct', '4,7500')
        ->call('saveProperty');

    $refreshedProperty = $createdProperty->fresh();

    expect($refreshedProperty->name)->toBe('7D')
        ->and((float) $refreshedProperty->community_pct)->toBe(3.5)
        ->and((float) $refreshedProperty->location_pct)->toBe(4.75);
});

it('requires and persists percentages for storage locations too', function () {
    $user = adminUser();

    $storageLocation = Location::factory()->create([
        'type' => 'storage',
        'code' => 'TR-A',
        'name' => 'Trastero A',
    ]);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $storageLocation])
        ->set('newPropertyName', 'S-1')
        ->set('newCommunityPct', '')
        ->set('newLocationPct', '')
        ->call('addProperty')
        ->assertHasErrors(['newCommunityPct' => 'required', 'newLocationPct' => 'required'])
        ->set('newCommunityPct', '1,5000')
        ->set('newLocationPct', '2,2500')
        ->call('addProperty')
        ->assertHasNoErrors();

    $createdStorageProperty = Property::query()
        ->where('location_id', $storageLocation->id)
        ->where('name', 'S-1')
        ->first();

    expect($createdStorageProperty)->not->toBeNull()
        ->and((float) $createdStorageProperty->community_pct)->toBe(1.5)
        ->and((float) $createdStorageProperty->location_pct)->toBe(2.25);
});

it('filters owners by active portal assignment', function () {
    $user = adminUser();

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

it('filters owners by active local assignment', function () {
    $user = adminUser();

    $localA = Location::factory()->create(['type' => 'local', 'code' => 'L-1', 'name' => 'Local L-1']);
    $localB = Location::factory()->create(['type' => 'local', 'code' => 'L-2', 'name' => 'Local L-2']);

    $propertyA = Property::factory()->create(['location_id' => $localA->id, 'name' => '1A']);
    $propertyB = Property::factory()->create(['location_id' => $localB->id, 'name' => '2B']);

    $ownerA = Owner::factory()->create(['coprop1_name' => 'Local Ane']);
    $ownerB = Owner::factory()->create(['coprop1_name' => 'Local Bea']);

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
        ->set('filterLocal', (string) $localA->id)
        ->assertSee('Local Ane')
        ->assertDontSee('Local Bea');
});

it('renders new admin pages for locations and owners', function () {
    $user = adminUser();

    $location = Location::factory()->create(['type' => 'portal', 'code' => '33-A', 'name' => 'Portal 33-A']);
    $owner = Owner::factory()->create();

    test()->actingAs($user)
        ->get(route('admin.locations.portals'))
        ->assertOk();

    test()->actingAs($user)
        ->get(route('admin.locations.locals'))
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

it('renders a type-aware breadcrumb link on location detail page', function () {
    $user = adminUser();

    $garage = Location::factory()->garage()->create(['code' => 'P-2', 'name' => 'Garaje P-2']);

    test()->actingAs($user)
        ->get(route('admin.locations.show', $garage))
        ->assertOk()
        ->assertSee(route('admin.locations.garages'), false);
});

it('creates a new owner from the admin owners list', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->portal()->create(['code' => '33-A', 'name' => 'Portal 33-A']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'name' => '1A']);

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('coprop1Name', 'Irati Lasa')
        ->set('coprop1Dni', '11223344A')
        ->set('coprop1Email', 'irati@example.com')
        ->set('coprop1Phone', '600123123')
        ->set('newAssignments.0.property_id', (string) $property->id)
        ->set('newAssignments.0.start_date', '2026-01-01')
        ->call('createOwner')
        ->assertSet('showCreateForm', false);

    $owner = Owner::query()->where('coprop1_dni', '11223344A')->first();

    expect($owner)->not->toBeNull();

    $createdUser = User::query()->where('email', 'irati@example.com')->first();

    expect($createdUser)->not->toBeNull();

    Mail::assertSent(OwnerWelcomeMail::class, function (OwnerWelcomeMail $mail): bool {
        return $mail->hasTo('irati@example.com');
    });

    expect($owner->assignments()->count())->toBe(1);
});

it('shows owners without active properties when using without-properties filter', function () {
    $user = adminUser();

    $portal = Location::factory()->portal()->create(['code' => '33-A', 'name' => 'Portal 33-A']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'name' => '1A']);

    $activeOwner = Owner::factory()->create(['coprop1_name' => 'Jabe Aktiboa']);
    $closedOwner = Owner::factory()->create(['coprop1_name' => 'Jabe Itxia']);
    $withoutAssignmentsOwner = Owner::factory()->create(['coprop1_name' => 'Jabetzarik Gabe']);

    PropertyAssignment::factory()->create([
        'property_id' => $property->id,
        'owner_id' => $activeOwner->id,
        'start_date' => now()->subMonth(),
        'end_date' => null,
    ]);

    $closedProperty = Property::factory()->create(['location_id' => $portal->id, 'name' => '1B']);
    PropertyAssignment::factory()->create([
        'property_id' => $closedProperty->id,
        'owner_id' => $closedOwner->id,
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('showWithoutProperties')
        ->assertSee('Jabe Itxia')
        ->assertSee('Jabetzarik Gabe')
        ->assertDontSee('Jabe Aktiboa');
});

it('renders owners list with inline expansion action instead of detail bars link', function () {
    $user = adminUser();
    $owner = Owner::factory()->create(['coprop1_name' => 'Inline Jabea']);
    $portal = Location::factory()->portal()->create(['code' => '33-D']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '3B',
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    test()->actingAs($user)
        ->get(route('admin.owners.index'))
        ->assertOk()
        ->assertDontSee('data-action="open-owner-detail"', false)
        ->assertSee('Inline Jabea');
});

it('allows creating and editing owner assignments inline from owners list', function () {
    $user = adminUser();

    $owner = Owner::factory()->create(['coprop1_name' => 'Jabe Inline']);
    $portal = Location::factory()->portal()->create(['code' => '33-C']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '2A',
    ]);

    $component = Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('toggleOwnerRow', $owner->id)
        ->assertSet('expandedOwnerId', $owner->id)
        ->set('inlinePropertyId', (string) $property->id)
        ->set('inlineStartDate', '2026-01-01')
        ->set('inlineEndDate', '')
        ->call('createInlineAssignment');

    $assignment = PropertyAssignment::query()
        ->where('owner_id', $owner->id)
        ->where('property_id', $property->id)
        ->first();

    expect($assignment)->not->toBeNull();

    $component
        ->set("assignmentEdits.$assignment->id.admin_validated", true)
        ->set("assignmentEdits.$assignment->id.owner_validated", true)
        ->call('saveAssignment', $assignment->id);

    $assignment->refresh();

    expect($assignment->admin_validated)->toBeTrue()
        ->and($assignment->owner_validated)->toBeTrue();

    $component
        ->set("assignmentEdits.$assignment->id.end_date", '2026-12-31')
        ->set("assignmentEdits.$assignment->id.admin_validated", false)
        ->set("assignmentEdits.$assignment->id.owner_validated", false)
        ->call('saveAssignment', $assignment->id);

    $assignment->refresh();

    expect($assignment->end_date?->format('Y-m-d'))->toBe('2026-12-31')
        ->and($assignment->admin_validated)->toBeTrue()
        ->and($assignment->owner_validated)->toBeTrue()
        ->and($owner->user->fresh()->is_active)->toBeFalse();
});
