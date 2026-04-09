<?php

namespace Database\Seeders;

use App\Models\Location;
use App\CommunityLocations;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = array_merge(
            array_map(
                static fn(string $code): array => ['type' => 'portal', 'code' => $code, 'name' => 'Portal ' . $code],
                CommunityLocations::PORTALS,
            ),
            array_map(
                static fn(string $code): array => ['type' => 'garage', 'code' => $code, 'name' => 'Garaje ' . $code],
                CommunityLocations::GARAGES,
            ),
            array_map(
                static fn(string $code): array => ['type' => 'storage', 'code' => $code, 'name' => 'Trastero ' . $code],
                CommunityLocations::STORAGES,
            ),
        );

        foreach ($locations as $data) {
            Location::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
