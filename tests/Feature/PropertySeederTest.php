<?php

use App\Models\Location;
use App\Models\Property;
use Database\Seeders\PropertySeeder;

it('seeds portal, local and garage properties with expected names', function () {
    $portal = Location::factory()->create([
        'type' => 'portal',
        'code' => '33-A',
        'name' => 'Portal 33-A',
    ]);

    $local = Location::factory()->create([
        'type' => 'local',
        'code' => 'L-1',
        'name' => 'Local L-1',
    ]);

    $garage = Location::factory()->create([
        'type' => 'garage',
        'code' => 'P-1',
        'name' => 'Garaje P-1',
    ]);

    $storage = Location::factory()->create([
        'type' => 'storage',
        'code' => 'A',
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
        'code' => '33-B',
        'name' => 'Portal 33-B',
    ]);

    app(PropertySeeder::class)->run();
    app(PropertySeeder::class)->run();

    expect(Property::where('location_id', $portal->id)->count())->toBe(18);
});
