<?php

// Feature: quality-db-query-guard
// Validates: Requisitos 2.1-2.4, 3.1-3.3, 6.1

use App\Models\Owner;
use Livewire\Livewire;
use App\Models\Property;
use App\Livewire\Admin\Owners;
use App\Models\PropertyAssignment;
use Tests\Feature\Performance\QueryGuardHelpers;

it('admin owners list stays within query budget', function () {
    // Budget: 20 queries — owners list loads user + owner + active assignments + properties
    // Margin: ~20% over expected baseline for 5 owners with 1 assignment each
    $user = adminUser();

    Owner::factory(5)->create()->each(function (Owner $owner) {
        $property = Property::factory()->create();
        PropertyAssignment::factory()->create([
            'owner_id' => $owner->id,
            'property_id' => $property->id,
            'end_date' => null,
        ]);
    });

    $log = QueryGuardHelpers::capture(function () use ($user) {
        Livewire::actingAs($user)
            ->test(Owners::class);
    });

    QueryGuardHelpers::assertMaxQueries($log, 20, 'Admin\Owners render');
});

it('admin owners list has no runaway duplicate statements', function () {
    // Duplicate limit: 5 — locations se carga una vez por tipo (portal/local/garage/storage = 4 queries esperadas)
    // Se excluyen checks de autorización de roles (overhead de framework Livewire)
    $user = adminUser();

    Owner::factory(5)->create()->each(function (Owner $owner) {
        $property = Property::factory()->create();
        PropertyAssignment::factory()->create([
            'owner_id' => $owner->id,
            'property_id' => $property->id,
            'end_date' => null,
        ]);
    });

    $log = QueryGuardHelpers::capture(function () use ($user) {
        Livewire::actingAs($user)
            ->test(Owners::class);
    });

    QueryGuardHelpers::assertMaxDuplicates($log, 5, 'Admin\Owners duplicates', [
        'role_user', // checks de autorización de framework
    ]);
});
