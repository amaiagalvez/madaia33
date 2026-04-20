<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Models\OwnerAuditLog;
use App\Livewire\Admin\Owners;
use App\Mail\OwnerWelcomeMail;
use App\Livewire\Admin\Locations;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Mail;
use App\Livewire\Admin\LocationDetail;
use App\Actions\Owners\CreateOwnerAction;

it('renders admin locations list for selected type', function () {
    $user = adminUser();

    Location::factory()->create(['type' => 'portal', 'name' => 'Portal 99-A']);
    Location::factory()->create(['type' => 'portal', 'name' => 'Portal 11-A']);
    Location::factory()->create(['type' => 'local', 'name' => 'Local L-1']);
    Location::factory()->create(['type' => 'garage', 'name' => 'Garaje P-1']);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSeeHtml('data-admin-filter-group')
        ->assertSeeHtml('data-admin-filter-button="portal"')
        ->assertSeeHtml('data-admin-table-header')
        ->assertSeeHtml('data-admin-action="edit"')
        ->assertSeeInOrder(['Portal 11-A', 'Portal 99-A'])
        ->assertDontSee('Local L-1')
        ->assertDontSee('Garaje P-1')
        ->call('setType', 'local')
        ->assertSee('Local L-1')
        ->assertDontSee('Portal 11-A')
        ->call('setType', 'garage')
        ->assertSee('Garaje P-1')
        ->assertDontSee('Portal 11-A');
});

it('opens and saves location edit form from locations list without navigating', function () {
    $user = adminUser();
    $location = Location::factory()->create(['name' => 'Portal 55-A']);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->call('openEditForm', $location->id)
        ->assertSet('showEditForm', true)
        ->assertSet('editingLocationId', $location->id)
        ->set('editName', 'Portal 55-B')
        ->call('saveEditForm')
        ->assertSet('showEditForm', false)
        ->assertSet('editingLocationId', null);

    $location->refresh();

    expect($location->name)->toBe('Portal 55-B');
});

it('creates a new location from the locations list side panel', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->set('type', 'local')
        ->call('createLocation')
        ->assertSet('showCreateForm', true)
        ->set('newName', 'Local 99')
        ->call('saveCreateForm')
        ->assertSet('showCreateForm', false);

    expect(Location::query()
        ->where('type', 'local')
        ->where('name', 'Local 99')
        ->exists())->toBeTrue();
});

it('opens delete confirmation and deletes location from locations list', function () {
    $user = adminUser();
    $location = Location::factory()->create(['name' => 'Portal 66-A']);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->call('confirmDelete', $location->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('confirmingDeleteId', $location->id)
        ->call('deleteLocation')
        ->assertSet('showDeleteModal', false)
        ->assertSet('confirmingDeleteId', null);

    expect(Location::query()->whereKey($location->id)->exists())->toBeFalse();
});

it('does not delete location when it still has linked properties', function () {
    $user = adminUser();

    $location = Location::factory()->create(['name' => 'Portal 77-A']);

    Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
    ]);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->call('confirmDelete', $location->id)
        ->call('deleteLocation')
        ->assertSee(__('admin.locations.delete_blocked_has_properties'));

    expect(Location::query()->whereKey($location->id)->exists())->toBeTrue();
});

it('renders location detail with assignment badges', function () {
    $user = adminUser();

    $location = Location::factory()->create(['name' => 'Portal 33-A']);
    $property = Property::factory()->create([
        'location_id' => $location->id,
        'code' => '1A',
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
        ->assertSee('Portal 33-A')
        ->assertSee('1A')
        ->assertDontSee('data-admin-validated=', false)
        ->assertDontSee('data-owner-validated=', false);
});

it('toggles assignment validations one by one and blocks closed assignments', function () {
    $user = adminUser();

    $owner = Owner::factory()->create();
    $location = Location::factory()->portal()->create(['name' => 'Portal 33-A']);

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

    $component = Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('toggleOwnerRow', $owner->id);

    $component
        ->set("assignmentEdits.{$activeAssignment->id}.admin_validated", true)
        ->call('saveAssignment', $activeAssignment->id);

    expect($activeAssignment->refresh()->admin_validated)->toBeTrue();

    $component
        ->set("assignmentEdits.{$activeAssignment->id}.owner_validated", true)
        ->call('saveAssignment', $activeAssignment->id);

    expect($activeAssignment->refresh()->owner_validated)->toBeTrue();

    $component
        ->set("assignmentEdits.{$closedAssignment->id}.admin_validated", true)
        ->call('saveAssignment', $closedAssignment->id);

    expect($closedAssignment->refresh()->admin_validated)->toBeFalse();

    $component
        ->set("assignmentEdits.{$closedAssignment->id}.end_date", '')
        ->set("assignmentEdits.{$closedAssignment->id}.admin_validated", true)
        ->set("assignmentEdits.{$closedAssignment->id}.owner_validated", true)
        ->call('saveAssignment', $closedAssignment->id);

    expect($closedAssignment->refresh()->end_date)->toBeNull()
        ->and($closedAssignment->admin_validated)->toBeFalse()
        ->and($closedAssignment->owner_validated)->toBeFalse();
});

it('accepts comma decimals for location percentages and stores them normalized with dot', function () {
    $user = adminUser();

    $location = Location::factory()->create(['name' => 'Portal 33-A']);

    $component = Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $location])
        ->set('newPropertyCode', '7C')
        ->set('newPropertyName', '7C')
        ->set('newCommunityPct', '1,2500')
        ->set('newLocationPct', '2,5000')
        ->call('addProperty');

    $createdProperty = Property::query()
        ->where('location_id', $location->id)
        ->where('code', '7C')
        ->where('name', '7C')
        ->first();

    expect($createdProperty)->not->toBeNull()
        ->and((float) $createdProperty->community_pct)->toBe(1.25)
        ->and((float) $createdProperty->location_pct)->toBe(2.5);

    $component
        ->call('startEditing', $createdProperty->id)
        ->set('editCode', '7D')
        ->set('editName', '7D')
        ->set('editCommunityPct', '3,5000')
        ->set('editLocationPct', '4,7500')
        ->call('saveProperty');

    $refreshedProperty = $createdProperty->fresh();

    expect($refreshedProperty->code)->toBe('7D')
        ->and($refreshedProperty->name)->toBe('7D')
        ->and((float) $refreshedProperty->community_pct)->toBe(3.5)
        ->and((float) $refreshedProperty->location_pct)->toBe(4.75);
});

it('opens add-property form and saves property for the currently opened location', function () {
    $user = adminUser();

    $openedLocation = Location::factory()->create(['name' => 'Portal Open']);
    $otherLocation = Location::factory()->create(['name' => 'Portal Other']);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $openedLocation])
        ->assertSet('showAddForm', false)
        ->call('openAddForm')
        ->assertSet('showAddForm', true)
        ->set('newPropertyCode', 'OPEN-1')
        ->set('newPropertyName', 'OPEN-1')
        ->set('newCommunityPct', '10')
        ->set('newLocationPct', '20')
        ->call('addProperty')
        ->assertSet('showAddForm', false);

    expect(Property::query()
        ->where('location_id', $openedLocation->id)
        ->where('code', 'OPEN-1')
        ->where('name', 'OPEN-1')
        ->exists())->toBeTrue()
        ->and(Property::query()
            ->where('location_id', $otherLocation->id)
            ->where('name', 'OPEN-1')
            ->exists())->toBeFalse();
});

it('requires and persists percentages for storage locations too', function () {
    $user = adminUser();

    $storageLocation = Location::factory()->storage()->create(['name' => 'Trastero A']);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $storageLocation])
        ->set('newPropertyCode', 'S-1')
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
        ->where('code', 'S-1')
        ->where('name', 'S-1')
        ->first();

    expect($createdStorageProperty)->not->toBeNull()
        ->and((float) $createdStorageProperty->community_pct)->toBe(1.5)
        ->and((float) $createdStorageProperty->location_pct)->toBe(2.25);
});

it('filters owners by active portal assignment', function () {
    $user = adminUser();

    $portalA = Location::factory()->create(['name' => 'Portal 33-A']);
    $portalB = Location::factory()->create(['name' => 'Portal 33-B']);

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

    $localA = Location::factory()->local()->create(['name' => 'Local L-1']);
    $localB = Location::factory()->local()->create(['name' => 'Local L-2']);

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

it('shows email and phone in owners coproprietary columns', function () {
    $user = adminUser();

    Owner::factory()->create([
        'coprop1_name' => 'Leire Nagusi',
        'coprop1_surname' => 'Goienetxe',
        'coprop1_email' => 'leire@example.com',
        'coprop1_phone' => '600111222',
        'coprop2_name' => 'Mikel Bigarren',
        'coprop2_surname' => 'Arregi',
        'coprop2_email' => 'mikel@example.com',
        'coprop2_phone' => '600333444',
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->assertSee('Leire Nagusi')
        ->assertSee('Goienetxe')
        ->assertSee('leire@example.com')
        ->assertSee('600111222')
        ->assertSee('Mikel Bigarren')
        ->assertSee('Arregi')
        ->assertSee('mikel@example.com')
        ->assertSee('600333444');
});

it('uses shared side-panel and footer pattern in owners forms', function () {
    $user = adminUser();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Panel Owner',
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('showCreateForm', true)
        ->assertSeeHtml('data-section="owner-create-form"')
        ->assertSeeHtml('data-admin-side-panel-form')
        ->assertSeeHtml('data-admin-form-footer-actions')
        ->set('showCreateForm', false)
        ->call('openEditOwnerForm', $owner->id)
        ->assertSeeHtml('data-section="owner-edit-form"')
        ->assertSeeHtml('data-admin-side-panel-form')
        ->assertSeeHtml('data-admin-form-footer-actions')
        ->assertSeeHtml('data-owner-shared-form="true"')
        ->assertSeeHtml('data-owner-shared-form-mode="wire"');
});

it('shows a compact owner audit log list in the edit form', function () {
    $user = adminUser();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Audit Owner',
    ]);

    OwnerAuditLog::query()->create([
        'owner_id' => $owner->id,
        'changed_by_user_id' => $user->id,
        'field' => 'coprop1_phone',
        'old_value' => '600111222',
        'new_value' => '699999999',
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    OwnerAuditLog::query()->create([
        'owner_id' => $owner->id,
        'changed_by_user_id' => null,
        'field' => 'language',
        'old_value' => 'es',
        'new_value' => 'eu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('openEditOwnerForm', $owner->id)
        ->assertSeeHtml('data-section="owner-audit-log"')
        ->assertSeeHtml('data-owner-audit-log-scroll')
        ->assertSee(__('admin.owners.audit.title'))
        ->assertSee(__('admin.owners.form.coprop1_phone'))
        ->assertSee('600111222')
        ->assertSee('699999999')
        ->assertSee(__('admin.owners.audit.system'));
});

it('uses shared styling components in owners inline assignments panel', function () {
    $user = adminUser();

    $owner = Owner::factory()->create(['coprop1_name' => 'Inline Styled Owner']);
    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-Z']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '5A',
        'community_pct' => 1.25,
        'location_pct' => 2.5,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('toggleOwnerRow', $owner->id)
        ->assertSeeHtml('data-owner-inline-panel="' . $owner->id . '"')
        ->assertSeeHtml('data-owner-inline-table="' . $owner->id . '"')
        ->assertSeeHtml('data-admin-table-header')
        ->assertSeeHtml('data-admin-date-input')
        ->assertSee('33-Z')
        ->assertSee('5A')
        ->assertSee('1,25%')
        ->assertSee('2,50%');
});

it('filters owners by text search across owner record fields', function () {
    $user = adminUser();

    $matchingOwner = Owner::factory()->create([
        'coprop1_name' => 'Nora Search',
        'coprop1_dni' => '12345678A',
        'coprop1_email' => 'nora.search@example.com',
        'coprop2_name' => 'Bigarren Nora',
    ]);

    $nonMatchingOwner = Owner::factory()->create([
        'coprop1_name' => 'Irati Normal',
        'coprop1_dni' => '87654321B',
        'coprop1_email' => 'irati.normal@example.com',
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('filterSearch', '12345678A')
        ->assertViewHas('owners', function ($owners) use ($matchingOwner): bool {
            return $owners->getCollection()->pluck('id')->all() === [$matchingOwner->id];
        })
        ->set('filterSearch', 'Bigarren Nora')
        ->assertViewHas('owners', function ($owners) use ($matchingOwner): bool {
            return $owners->getCollection()->pluck('id')->all() === [$matchingOwner->id];
        })
        ->set('filterSearch', (string) $matchingOwner->id)
        ->assertViewHas('owners', function ($owners) use ($matchingOwner): bool {
            return $owners->getCollection()->pluck('id')->contains($matchingOwner->id);
        })
        ->set('filterSearch', (string) $nonMatchingOwner->id)
        ->assertViewHas('owners', function ($owners) use ($nonMatchingOwner): bool {
            return $owners->getCollection()->pluck('id')->contains($nonMatchingOwner->id);
        });
});

it('renders new admin pages for locations and owners', function () {
    $user = adminUser();

    $location = Location::factory()->create(['name' => 'Portal 33-A']);
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
});

it('renders a type-aware breadcrumb link on location detail page', function () {
    $user = adminUser();

    $garage = Location::factory()->garage()->create(['name' => 'Garaje P-2']);

    test()->actingAs($user)
        ->get(route('admin.locations.show', $garage))
        ->assertOk()
        ->assertSee(route('admin.locations.garages'), false);
});

it('creates a new owner from the admin owners list', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-A']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'code' => '1A', 'name' => '1A']);

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('coprop1Name', 'Irati Lasa')
        ->set('coprop1Surname', 'Etxaniz')
        ->set('coprop1Dni', '11223344A')
        ->set('coprop1Email', 'irati@example.com')
        ->set('coprop1Phone', '600123123')
        ->set('newAssignments.0.property_id', (string) $property->id)
        ->set('newAssignments.0.start_date', '2026-01-01')
        ->call('createOwner')
        ->assertSet('showCreateForm', false);

    $owner = Owner::query()->where('coprop1_dni', '11223344A')->first();

    expect($owner)->not->toBeNull();
    expect($owner->coprop1_surname)->toBe('Etxaniz');

    $createdUser = User::query()->where('email', 'irati@example.com')->first();

    expect($createdUser)->not->toBeNull();

    Mail::assertSent(OwnerWelcomeMail::class, function (OwnerWelcomeMail $mail): bool {
        return $mail->hasTo('irati@example.com');
    });

    expect($owner->welcome)->toBeTrue()
        ->and($owner->assignments()->count())->toBe(1);
});

it('creates a new owner without email and does not send welcome email', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->create(['name' => 'Portal 33-B']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'name' => '1B']);

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('coprop1Name', 'Owner Sin Email')
        ->set('coprop1Dni', '11223344Z')
        ->set('coprop1Email', '')
        ->set('coprop1Phone', '600123999')
        ->set('newAssignments.0.property_id', (string) $property->id)
        ->set('newAssignments.0.start_date', '2026-01-01')
        ->call('createOwner')
        ->assertSet('warningMessage', __('admin.owners.welcome_not_sent_missing_email'));

    $owner = Owner::query()->where('coprop1_dni', '11223344Z')->first();

    expect($owner)->not->toBeNull();
    expect($owner?->welcome)->toBeFalse();

    Mail::assertNothingSent();
});

it('resends the owner welcome email from the owners list', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->create(['name' => 'Portal 77-W']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'name' => '2B']);

    $owner = (new CreateOwnerAction)->execute([
        'coprop1_name' => 'Ane Iriarte',
        'coprop1_surname' => 'Goikoetxea',
        'coprop1_dni' => '55443322K',
        'coprop1_email' => 'ane@example.com',
        'assignments' => [
            [
                'property_id' => $property->id,
                'start_date' => '2026-01-01',
                'end_date' => null,
            ],
        ],
    ]);

    Mail::assertSentCount(1);

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->call('confirmResendWelcomeMail', $owner->id)
        ->assertSet('showWelcomeModal', true)
        ->call('doResendWelcomeMail')
        ->assertSet('showWelcomeModal', false)
        ->assertHasNoErrors();

    Mail::assertSentCount(2);

    expect($owner->fresh()->welcome)->toBeTrue();
});

it('does not resend owner welcome email when owner has no email', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->create(['name' => 'Portal 77-X']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'name' => '2C']);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner No Mail',
        'coprop1_email' => '',
        'welcome' => false,
    ]);

    $owner->user?->forceFill(['email' => ''])->save();

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->call('confirmResendWelcomeMail', $owner->id)
        ->assertSet('showWelcomeModal', true)
        ->call('doResendWelcomeMail')
        ->assertSet('showWelcomeModal', false)
        ->assertSet('warningMessage', __('admin.owners.welcome_not_sent_missing_email'));

    expect($owner->fresh()->welcome)->toBeFalse();
    Mail::assertNothingSent();
});

it('creates a new owner without exposing or setting a manual owner id', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-A']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'code' => '1A', 'name' => '1A']);

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('coprop1Name', 'Automatic Id Owner')
        ->set('coprop1Dni', '11223344B')
        ->set('coprop1Email', 'automatic-id@example.com')
        ->set('coprop1Phone', '600123124')
        ->set('newAssignments.0.property_id', (string) $property->id)
        ->set('newAssignments.0.start_date', '2026-01-01')
        ->call('createOwner')
        ->assertSet('showCreateForm', false);

    $owner = Owner::query()->where('coprop1_email', 'automatic-id@example.com')->first();

    expect($owner)->not->toBeNull()
        ->and($owner->id)->toBeGreaterThan(0)
        ->and($owner->coprop1_email)->toBe('automatic-id@example.com');
});

it('shows owners without active properties when using without-properties filter', function () {
    $user = adminUser();

    $portal = Location::factory()->create(['name' => 'Portal 33-A']);
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

it('shows only properties without active owners in owner property selectors', function () {
    $user = adminUser();

    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-S']);

    $availableProperty = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'FREE-1',
    ]);

    $assignedProperty = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'BLOCKED-1',
    ]);

    $assignedOwner = Owner::factory()->create(['coprop1_name' => 'Assigned Owner']);

    PropertyAssignment::factory()->create([
        'owner_id' => $assignedOwner->id,
        'property_id' => $assignedProperty->id,
        'end_date' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('showCreateForm', true)
        ->assertViewHas('assignableProperties', function ($properties) use ($availableProperty, $assignedProperty): bool {
            $ids = $properties->pluck('id');

            return $ids->contains($availableProperty->id)
                && ! $ids->contains($assignedProperty->id)
                && $ids->count() === 1;
        });
});

it('stores primary dni as null when admin owner edit sends empty dni', function () {
    $user = adminUser();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner Dni Admin',
        'coprop1_email' => 'owner.dni.admin@example.com',
        'coprop1_dni' => '11223344A',
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('openEditOwnerForm', $owner->id)
        ->set('editCoprop1Name', 'Owner Dni Admin Updated')
        ->set('editCoprop1Email', 'owner.dni.admin.updated@example.com')
        ->set('editCoprop1Dni', '')
        ->call('saveEditOwner')
        ->assertHasNoErrors();

    expect($owner->fresh()->coprop1_dni)->toBeNull();
});

it('sanitizes dni and phone fields when creating owner from admin list', function () {
    Mail::fake();

    $adminUser = adminUser();
    $portal = Location::factory()->create(['name' => 'Portal 33-S']);
    $property = Property::factory()->create(['location_id' => $portal->id, 'name' => '3C']);

    Livewire::actingAs($adminUser)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('coprop1Name', 'Sanitized Owner')
        ->set('coprop1Dni', '11.22-33 44a')
        ->set('coprop1Email', 'sanitize-owner@example.com')
        ->set('coprop1Phone', '600 12-31.23')
        ->set('newAssignments.0.property_id', (string) $property->id)
        ->set('newAssignments.0.start_date', '2026-01-01')
        ->call('createOwner')
        ->assertHasNoErrors();

    $owner = Owner::query()->where('coprop1_email', 'sanitize-owner@example.com')->first();

    expect($owner)->not->toBeNull()
        ->and($owner?->coprop1_dni)->toBe('11223344A')
        ->and($owner?->coprop1_phone)->toBe('600123123');
});

it('sanitizes dni and phone fields when editing owner from admin list', function () {
    $user = adminUser();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner Dni Phone Admin',
        'coprop1_email' => 'owner.dni.phone.admin@example.com',
        'coprop1_dni' => '11223344A',
        'coprop1_phone' => '600111222',
        'coprop2_dni' => '22334455B',
        'coprop2_phone' => '611222333',
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('openEditOwnerForm', $owner->id)
        ->set('editCoprop1Name', 'Owner Dni Phone Admin Updated')
        ->set('editCoprop1Email', 'owner.dni.phone.admin.updated@example.com')
        ->set('editCoprop1Dni', '11.22-33 44z')
        ->set('editCoprop1Phone', '600 99-88.77')
        ->set('editCoprop2Dni', ' 99.88-77 66x')
        ->set('editCoprop2Phone', '611-00 11.22')
        ->call('saveEditOwner')
        ->assertHasNoErrors();

    $owner->refresh();

    expect($owner->coprop1_dni)->toBe('11223344Z')
        ->and($owner->coprop1_phone)->toBe('600998877')
        ->and($owner->coprop2_dni)->toBe('99887766X')
        ->and($owner->coprop2_phone)->toBe('611001122');
});

it('updates has_whatsapp fields and shows invalid-contact warnings in admin owner edit', function () {
    $user = adminUser();

    $owner = Owner::factory()->create([
        'coprop1_phone_invalid' => true,
        'coprop1_email_invalid' => true,
        'coprop2_phone_invalid' => true,
        'coprop2_email_invalid' => true,
        'coprop1_has_whatsapp' => false,
        'coprop2_has_whatsapp' => false,
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('openEditOwnerForm', $owner->id)
        ->assertSee(__('admin.owners.form.phone_invalid_warning'))
        ->assertSee(__('admin.owners.form.email_invalid_warning'))
        ->set('editCoprop1HasWhatsapp', true)
        ->set('editCoprop2HasWhatsapp', true)
        ->call('saveEditOwner')
        ->assertHasNoErrors();

    expect($owner->fresh()->coprop1_has_whatsapp)->toBeTrue()
        ->and($owner->fresh()->coprop2_has_whatsapp)->toBeTrue();
});

it('renders owners list with inline expansion action instead of detail bars link', function () {
    $user = adminUser();
    $owner = Owner::factory()->create(['coprop1_name' => 'Inline Jabea']);
    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-D']);
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

it('shows accepted terms indicator in owners list', function () {
    $user = adminUser();

    $location = Location::factory()->portal()->create(['name' => 'Portal 33-T']);
    $propertyAccepted = Property::factory()->create([
        'location_id' => $location->id,
        'name' => 'T-1',
    ]);
    $propertyPending = Property::factory()->create([
        'location_id' => $location->id,
        'name' => 'T-2',
    ]);

    $acceptedOwner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    $pendingOwner = Owner::factory()->create([
        'accepted_terms_at' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $acceptedOwner->id,
        'property_id' => $propertyAccepted->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $pendingOwner->id,
        'property_id' => $propertyPending->id,
        'end_date' => null,
    ]);

    test()->actingAs($user)
        ->get(route('admin.owners.index'))
        ->assertOk()
        ->assertSee('data-owner-terms-accepted="' . $acceptedOwner->id . '"', false)
        ->assertSee('data-owner-terms-accepted="' . $pendingOwner->id . '"', false);
});

it('shows assignment percentages in owner property-type columns with validation colors', function () {
    $user = adminUser();

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner Percentages',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'Portal PT-02']);
    $local = Location::factory()->local()->create(['name' => 'Local LC-02']);
    $garage = Location::factory()->garage()->create(['name' => 'Garage GR-02']);
    $storage = Location::factory()->storage()->create(['name' => 'Storage ST-02']);

    $portalProperty = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'P-2',
        'community_pct' => 11.5,
        'location_pct' => 21.75,
    ]);

    $localProperty = Property::factory()->create([
        'location_id' => $local->id,
        'name' => 'L-2',
        'community_pct' => 31,
        'location_pct' => 41,
    ]);

    $garageProperty = Property::factory()->create([
        'location_id' => $garage->id,
        'name' => 'G-2',
        'community_pct' => 6.25,
        'location_pct' => 7.5,
    ]);

    $storageProperty = Property::factory()->create([
        'location_id' => $storage->id,
        'name' => 'S-2',
        'community_pct' => 2,
        'location_pct' => 3,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $portalProperty->id,
        'end_date' => null,
        'admin_validated' => true,
        'owner_validated' => true,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $localProperty->id,
        'end_date' => null,
        'admin_validated' => false,
        'owner_validated' => false,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $garageProperty->id,
        'end_date' => null,
        'admin_validated' => true,
        'owner_validated' => true,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $storageProperty->id,
        'end_date' => null,
        'admin_validated' => false,
        'owner_validated' => false,
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->set('filterSearch', 'Owner Percentages')
        ->assertSee('[P-2]')
        ->assertSee('P-2')
        ->assertSee('21,75%')
        ->assertSee('11,50%')
        ->assertSee('[G-2]')
        ->assertSee('G-2')
        ->assertSee('7,50%')
        ->assertSee('6,25%')
        ->assertSee('[S-2]')
        ->assertSee('S-2')
        ->assertSee('3,00%')
        ->assertSee('2,00%')
        ->assertSee('[L-2]')
        ->assertSee('L-2')
        ->assertSee('41,00%')
        ->assertSee('31,00%')
        ->assertSeeInOrder([
            __('admin.owners.columns.portals'),
            __('admin.owners.columns.garages'),
            __('admin.owners.columns.storages'),
            __('admin.owners.columns.locals'),
        ])
        ->assertSeeHtml('data-owner-assignment-type="portal"')
        ->assertSeeHtml('data-owner-assignment-type="garage"')
        ->assertSeeHtml('data-owner-assignment-type="storage"')
        ->assertSeeHtml('data-owner-assignment-type="local"')
        ->assertSeeHtml('text-green-600')
        ->assertSeeHtml('text-red-500');
});

it('orders owners by portal assignment ascending and keeps owners without portals last', function () {
    $user = adminUser();

    $portalA = Location::factory()->portal()->create(['name' => 'Portal A']);
    $portalB = Location::factory()->portal()->create(['name' => 'Portal B']);
    $garage = Location::factory()->garage()->create(['name' => 'Garage A']);

    $portalPropertyA = Property::factory()->create(['location_id' => $portalA->id, 'code' => 'A-01', 'name' => 'Portal Property A']);
    $portalPropertyB = Property::factory()->create(['location_id' => $portalB->id, 'code' => 'B-01', 'name' => 'Portal Property B']);
    $garageProperty = Property::factory()->create(['location_id' => $garage->id, 'code' => 'G-01', 'name' => 'Garage Property']);

    $ownerSecond = Owner::factory()->create(['coprop1_name' => 'Second Portal']);
    $ownerFirst = Owner::factory()->create(['coprop1_name' => 'First Portal']);
    $ownerWithoutPortal = Owner::factory()->create(['coprop1_name' => 'Without Portal']);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerSecond->id,
        'property_id' => $portalPropertyB->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerFirst->id,
        'property_id' => $portalPropertyA->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerWithoutPortal->id,
        'property_id' => $garageProperty->id,
        'end_date' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->set('filterStatus', 'all')
        ->assertViewHas('owners', function ($owners) use ($ownerFirst, $ownerSecond, $ownerWithoutPortal): bool {
            return $owners->getCollection()->pluck('id')->all() === [
                $ownerFirst->id,
                $ownerSecond->id,
                $ownerWithoutPortal->id,
            ];
        });
});

it('allows creating and editing owner assignments inline from owners list', function () {
    $user = adminUser();

    $owner = Owner::factory()->create(['coprop1_name' => 'Jabe Inline']);
    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-C']);
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

it('prevents reopening a closed assignment when the property already has another active owner', function () {
    $user = adminUser();

    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-R']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '4A',
    ]);

    $closedOwner = Owner::factory()->create();
    $activeOwner = Owner::factory()->create();

    $closedAssignment = PropertyAssignment::factory()->create([
        'owner_id' => $closedOwner->id,
        'property_id' => $property->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $activeOwner->id,
        'property_id' => $property->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
    ]);

    $closedOwner->user()->update(['is_active' => false]);

    Livewire::actingAs($user)
        ->test(Owners::class)
        ->call('toggleOwnerRow', $closedOwner->id)
        ->set("assignmentEdits.$closedAssignment->id.end_date", '')
        ->call('saveAssignment', $closedAssignment->id)
        ->assertSet('rowErrorMessage', __('La propiedad ya tiene una propietaria activa. Cierra la asignación anterior antes de asignar una nueva.'));

    $closedAssignment->refresh();

    expect($closedAssignment->end_date?->format('Y-m-d'))->toBe('2025-12-31')
        ->and($closedOwner->user->fresh()->is_active)->toBeFalse();
});

it('displays aggregated community and location percentages in locations listing', function () {
    $user = adminUser();

    $portal = Location::factory()->create(['name' => 'Portal 33-A']);

    // Create properties with known percentages
    Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '1A',
        'community_pct' => 1.5,
        'location_pct' => 2.0,
    ]);

    Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '1B',
        'community_pct' => 2.5,
        'location_pct' => 3.5,
    ]);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSee('4.00')
        ->assertSee('5.50');
});

it('shows chief property column value and fallback in locations listing', function () {
    $user = adminUser();

    $locationWithChief = Location::factory()->create(['name' => 'Portal 33-A']);
    $locationWithoutChief = Location::factory()->create(['name' => 'Portal 33-B']);

    $chiefProperty = Property::factory()->create([
        'location_id' => $locationWithChief->id,
        'name' => '2B',
    ]);

    $chiefUser = User::factory()->create();
    $chiefOwner = Owner::factory()->create([
        'user_id' => $chiefUser->id,
        'coprop1_name' => 'Jefa 33-A',
    ]);

    Role::query()->firstOrCreate([
        'name' => Role::COMMUNITY_ADMIN,
    ]);

    $chiefUser->assignRole(Role::COMMUNITY_ADMIN);
    $chiefUser->managedLocations()->syncWithoutDetaching([$locationWithChief->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $chiefOwner->id,
        'property_id' => $chiefProperty->id,
        'end_date' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSee(__('admin.locations.chief_property'))
        ->assertSee(__('admin.locations.community_admin_name'))
        ->assertSeeHtml('data-chief-property-for="' . $locationWithChief->id . '"')
        ->assertSeeHtml('data-chief-property-for="' . $locationWithoutChief->id . '"')
        ->assertSeeHtml('data-community-admin-for="' . $locationWithChief->id . '"')
        ->assertSeeHtml('data-community-admin-for="' . $locationWithoutChief->id . '"')
        ->assertSee('2B')
        ->assertSee('Jefa 33-A')
        ->assertSee('—');
});

it('shows red warning in locations listing when community percentages do not sum to 100%', function () {
    $user = adminUser();

    $location = Location::factory()->create(['type' => 'portal', 'name' => 'Portal 33-A']);

    // Properties that don't sum to 100%
    Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
        'community_pct' => 30.0,
        'location_pct' => 50.0,
    ]);

    Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1B',
        'community_pct' => 40.0,
        'location_pct' => 50.0,
    ]);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSee(__('admin.locations.community_pct_must_be_100'))
        ->assertSeeHtml('text-red-700');
});

it('does not show warning in locations listing when all community percentages sum to 100%', function () {
    $user = adminUser();

    $location = Location::factory()->create(['type' => 'portal', 'name' => 'Portal 33-A']);

    // Properties that sum exactly to 100%
    Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1A',
        'community_pct' => 50.0,
        'location_pct' => 50.0,
    ]);

    Property::factory()->create([
        'location_id' => $location->id,
        'name' => '1B',
        'community_pct' => 50.0,
        'location_pct' => 50.0,
    ]);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertDontSee(__('admin.locations.community_pct_must_be_100'));
});

it('shows invalid location percentage total in listing when not equal to 100%', function () {
    $user = adminUser();

    $portalInvalid = Location::factory()->create(['name' => 'Portal Invalid']);

    Property::factory()->create([
        'location_id' => $portalInvalid->id,
        'name' => '1A',
        'community_pct' => 40.0,
        'location_pct' => 60.0, // Should be 100%
    ]);

    Livewire::actingAs($user)
        ->test(Locations::class)
        ->assertSee('Portal Invalid')
        ->assertSee('60.00');
});

it('shows only properties with active owners in the same portal or garage as chief candidates', function () {
    $user = adminUser();

    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-H']);
    $otherPortal = Location::factory()->portal()->create(['name' => 'Portal 33-Z']);

    $portalPropertyA = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'H-1',
    ]);
    $portalPropertyB = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'H-2',
    ]);
    $otherPortalProperty = Property::factory()->create([
        'location_id' => $otherPortal->id,
        'name' => 'Z-1',
    ]);

    $candidateA = Owner::factory()->create(['coprop1_name' => 'Kandidata A']);
    $candidateB = Owner::factory()->create(['coprop1_name' => 'Kandidata B']);
    $inactiveOwner = Owner::factory()->create(['coprop1_name' => 'Inaktiboa']);
    $otherLocationOwner = Owner::factory()->create(['coprop1_name' => 'Beste Kokalekua']);

    PropertyAssignment::factory()->create([
        'owner_id' => $candidateA->id,
        'property_id' => $portalPropertyA->id,
        'end_date' => null,
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $candidateB->id,
        'property_id' => $portalPropertyB->id,
        'end_date' => null,
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $inactiveOwner->id,
        'property_id' => $portalPropertyA->id,
        'end_date' => now()->subDay(),
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $otherLocationOwner->id,
        'property_id' => $otherPortalProperty->id,
        'end_date' => null,
    ]);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $portal])
        ->assertSee('H-1')
        ->assertSee('H-2')
        ->assertDontSee('Inaktiboa')
        ->assertDontSee('Beste Kokalekua')
        ->assertDontSee('Z-1');
});

it('transfers chief and COMMUNITY_ADMIN role from previous owner to new owner in the same location', function () {
    $user = adminUser();

    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);

    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-R']);
    $propertyA = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'R-1',
    ]);
    $propertyB = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'R-2',
    ]);

    $previousChief = Owner::factory()->create(['coprop1_name' => 'Aurreko Jefa']);
    $newChief = Owner::factory()->create(['coprop1_name' => 'Jefa Berria']);

    PropertyAssignment::factory()->create([
        'owner_id' => $previousChief->id,
        'property_id' => $propertyA->id,
        'end_date' => null,
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $newChief->id,
        'property_id' => $propertyB->id,
        'end_date' => null,
    ]);

    $previousChiefUser = $previousChief->user()->firstOrFail();
    $previousChiefUser->assignRole(Role::COMMUNITY_ADMIN);
    $previousChiefUser->managedLocations()->sync([$portal->id]);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $portal])
        ->set('chiefPropertyId', (string) $propertyB->id)
        ->call('saveChiefOwner')
        ->assertHasNoErrors();

    $newChiefUser = $newChief->user()->firstOrFail()->fresh(['roles', 'managedLocations']);
    $previousChiefUser = $previousChiefUser->fresh(['roles', 'managedLocations']);

    expect($newChiefUser->hasRole(Role::COMMUNITY_ADMIN))->toBeTrue()
        ->and($newChiefUser->managedLocations()->whereKey($portal->id)->exists())->toBeTrue()
        ->and($previousChiefUser->managedLocations()->whereKey($portal->id)->exists())->toBeFalse()
        ->and($previousChiefUser->hasRole(Role::COMMUNITY_ADMIN))->toBeFalse();
});

it('keeps COMMUNITY_ADMIN role for previous chief when she still manages another location', function () {
    $user = adminUser();

    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);

    $portal = Location::factory()->portal()->create(['name' => 'Portal 33-K']);
    $garage = Location::factory()->garage()->create(['name' => 'Garage P-K']);

    $portalPropertyA = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'K-1',
    ]);
    $portalPropertyB = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => 'K-2',
    ]);
    $garageProperty = Property::factory()->create([
        'location_id' => $garage->id,
        'name' => 'PK-1',
    ]);

    $previousChief = Owner::factory()->create(['coprop1_name' => 'Jefa Mantendu']);
    $newChief = Owner::factory()->create(['coprop1_name' => 'Jefa Berria K']);

    PropertyAssignment::factory()->create([
        'owner_id' => $previousChief->id,
        'property_id' => $portalPropertyA->id,
        'end_date' => null,
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $previousChief->id,
        'property_id' => $garageProperty->id,
        'end_date' => null,
    ]);
    PropertyAssignment::factory()->create([
        'owner_id' => $newChief->id,
        'property_id' => $portalPropertyB->id,
        'end_date' => null,
    ]);

    $previousChiefUser = $previousChief->user()->firstOrFail();
    $previousChiefUser->assignRole(Role::COMMUNITY_ADMIN);
    $previousChiefUser->managedLocations()->sync([$portal->id, $garage->id]);

    Livewire::actingAs($user)
        ->test(LocationDetail::class, ['location' => $portal])
        ->set('chiefPropertyId', (string) $portalPropertyB->id)
        ->call('saveChiefOwner')
        ->assertHasNoErrors();

    $previousChiefUser = $previousChiefUser->fresh(['roles', 'managedLocations']);

    expect($previousChiefUser->managedLocations()->whereKey($portal->id)->exists())->toBeFalse()
        ->and($previousChiefUser->managedLocations()->whereKey($garage->id)->exists())->toBeTrue()
        ->and($previousChiefUser->hasRole(Role::COMMUNITY_ADMIN))->toBeTrue();
});
