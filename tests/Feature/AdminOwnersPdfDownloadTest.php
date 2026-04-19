<?php

use Mockery;
use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Location;
use App\Models\Property;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;

it('allows superadmin to download owners pdf from admin route', function () {
    $user = adminUser();

    test()->actingAs($user)
        ->get(route('admin.owners.pdf'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('forbids non superadmin users from downloading owners pdf', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($user)
        ->get(route('admin.owners.pdf'))
        ->assertForbidden();
});

it('applies active owners filters to the downloaded pdf', function () {
    $user = adminUser();

    $matchingOwner = Owner::factory()->create([
        'coprop1_name' => 'Filtro PDF Coincide',
    ]);

    $excludedOwner = Owner::factory()->create([
        'coprop1_name' => 'Filtro PDF Excluido',
    ]);

    $location = Location::factory()->portal()->create();

    $matchingProperty = Property::factory()->create([
        'location_id' => $location->id,
    ]);

    $excludedProperty = Property::factory()->create([
        'location_id' => $location->id,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $matchingOwner->id,
        'property_id' => $matchingProperty->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $excludedOwner->id,
        'property_id' => $excludedProperty->id,
        'end_date' => null,
    ]);

    $pdfMock = Mockery::mock(Barryvdh\DomPDF\PDF::class);
    $pdfMock->shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
    $pdfMock->shouldReceive('output')->andReturn('%PDF-mock');

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(function (string $view, array $payload) use ($matchingOwner, $excludedOwner): bool {
            if ($view !== 'pdf.owners.list') {
                return false;
            }

            if (! isset($payload['owners'], $payload['appliedFilters'])) {
                return false;
            }

            $owners = $payload['owners'];
            $appliedFilters = $payload['appliedFilters'];

            if (! $owners instanceof Collection || ! is_array($appliedFilters)) {
                return false;
            }

            $ids = $owners->pluck('id')->all();
            $filtersText = implode(' ', $appliedFilters);

            return in_array($matchingOwner->id, $ids, true)
            && ! in_array($excludedOwner->id, $ids, true)
            && str_contains($filtersText, 'Coincide');
        })
        ->andReturn($pdfMock);

    test()->actingAs($user)
        ->get(route('admin.owners.pdf', [
            'filter_status' => 'active',
            'filter_search' => 'Coincide',
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
