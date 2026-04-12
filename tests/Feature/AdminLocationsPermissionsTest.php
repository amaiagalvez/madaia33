<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Livewire\Admin\Locations;
use App\Livewire\Admin\LocationDetail;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('allows general admin to access locations index routes in read mode', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($generalAdmin)
        ->get(route('admin.locations.portals'))
        ->assertOk();
});

it('forbids create edit and delete location actions for general admin', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $location = Location::factory()->portal()->create();

    Livewire::actingAs($generalAdmin)
        ->test(Locations::class, ['type' => 'portal'])
        ->assertDontSeeHtml('data-admin-action="edit"')
        ->assertDontSeeHtml('data-admin-action="delete"')
        ->call('createLocation')
        ->assertForbidden();

    Livewire::actingAs($generalAdmin)
        ->test(Locations::class, ['type' => 'portal'])
        ->call('openEditForm', $location->id)
        ->assertForbidden();

    Livewire::actingAs($generalAdmin)
        ->test(Locations::class, ['type' => 'portal'])
        ->call('confirmDelete', $location->id)
        ->assertForbidden();
});

it('forbids create edit and delete location actions for community admin', function () {
    $communityAdmin = User::factory()->create();
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $managedLocation = Location::factory()->portal()->create();
    $communityAdmin->managedLocations()->sync([$managedLocation->id]);

    Livewire::actingAs($communityAdmin)
        ->test(Locations::class, ['type' => 'portal'])
        ->call('createLocation')
        ->assertForbidden();

    Livewire::actingAs($communityAdmin)
        ->test(Locations::class, ['type' => 'portal'])
        ->call('openEditForm', $managedLocation->id)
        ->assertForbidden();

    Livewire::actingAs($communityAdmin)
        ->test(Locations::class, ['type' => 'portal'])
        ->call('confirmDelete', $managedLocation->id)
        ->assertForbidden();
});

it('forbids property create and edit actions for general admin in location detail', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $location = Location::factory()->portal()->create();
    $property = Property::factory()->create([
        'location_id' => $location->id,
    ]);

    Livewire::actingAs($generalAdmin)
        ->test(LocationDetail::class, ['location' => $location])
        ->assertDontSee(__('admin.locations.add_property'))
        ->assertDontSeeHtml('data-admin-action="edit"')
        ->call('openAddForm')
        ->assertForbidden();

    Livewire::actingAs($generalAdmin)
        ->test(LocationDetail::class, ['location' => $location])
        ->call('startEditing', $property->id)
        ->assertForbidden();
});

it('forbids chief assignment update for general admin in location detail', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $location = Location::factory()->portal()->create();

    Livewire::actingAs($generalAdmin)
        ->test(LocationDetail::class, ['location' => $location])
        ->assertDontSee(__('admin.locations.chief_owner_save'))
        ->call('saveChiefOwner')
        ->assertForbidden();
});
