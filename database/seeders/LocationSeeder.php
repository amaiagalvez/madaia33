<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $portalNames = ['33-A', '33-B', '33-C', '33-D', '33-E', '33-F', '33-G', '33-H', '33-I', '33-J'];
        $localNames = ['L-1', 'L-2', 'L-3', 'L-4'];
        $garageNames = ['P-1', 'P-2', 'P-3'];
        $storageNames = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        $locations = array_merge(
            array_map(
                static fn (string $name): array => ['type' => 'portal', 'name' => 'Portal ' . $name],
                $portalNames,
            ),
            array_map(
                static fn (string $name): array => ['type' => 'local', 'name' => 'Local ' . $name],
                $localNames,
            ),
            array_map(
                static fn (string $name): array => ['type' => 'garage', 'name' => 'Garaje ' . $name],
                $garageNames,
            ),
            array_map(
                static fn (string $name): array => ['type' => 'storage', 'name' => 'Trastero ' . $name],
                $storageNames,
            ),
        );

        foreach ($locations as $data) {
            Location::firstOrCreate([
                'type' => $data['type'],
                'name' => $data['name'],
            ]);
        }
    }
}
