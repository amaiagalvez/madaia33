<?php

use App\Models\Location;
use App\Models\Property;
use Database\Seeders\PropertySeeder;

it('seeds portal, local and garage properties with expected names', function () {
    $portal = Location::factory()->create([
        'type' => 'portal',
        'name' => 'Portal 33-A',
    ]);

    $local = Location::factory()->create([
        'type' => 'local',
        'name' => 'Local L-1',
    ]);

    $garage = Location::factory()->create([
        'type' => 'garage',
        'name' => 'Garaje P-1',
    ]);

    $storage = Location::factory()->create([
        'type' => 'storage',
        'name' => 'Trastero A',
    ]);

    app(PropertySeeder::class)->run();

    expect(Property::where('location_id', $portal->id)->count())->toBe(18)
        ->and(Property::where('location_id', $portal->id)->where('name', '1-A')->exists())->toBeTrue()
        ->and(Property::where('location_id', $portal->id)->where('name', '6-C')->exists())->toBeTrue()
        ->and(Property::where('location_id', $local->id)->count())->toBe(18)
        ->and(Property::where('location_id', $local->id)->where('name', '1-A')->exists())->toBeTrue()
        ->and(Property::where('location_id', $local->id)->where('name', '6-C')->exists())->toBeTrue()
        ->and(Property::where('location_id', $garage->id)->count())->toBe(180)
        ->and(Property::where('location_id', $garage->id)->where('name', '1')->exists())->toBeTrue()
        ->and(Property::where('location_id', $garage->id)->where('name', '180')->exists())->toBeTrue()
        ->and(Property::where('location_id', $storage->id)->count())->toBe(0);
});

it('is idempotent when run multiple times', function () {
    $portal = Location::factory()->create([
        'type' => 'portal',
        'name' => 'Portal 33-B',
    ]);

    app(PropertySeeder::class)->run();
    app(PropertySeeder::class)->run();

    expect(Property::where('location_id', $portal->id)->count())->toBe(18);
});

it('does not modify existing property data when records already exist', function () {
    $portal = Location::factory()->create([
        'type' => 'portal',
        'name' => 'Portal 33-C',
    ]);

    $existing = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '1-A',
        'community_pct' => 9.99,
        'location_pct' => 8.88,
    ]);

    app(PropertySeeder::class)->run();

    $existing->refresh();

    expect((float) $existing->community_pct)->toBe(9.99)
        ->and((float) $existing->location_pct)->toBe(8.88);
});
