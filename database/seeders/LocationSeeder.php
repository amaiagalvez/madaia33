<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $portalCodes = ['33-A', '33-B', '33-C', '33-D', '33-E', '33-F', '33-G', '33-H', '33-I', '33-J'];
        $garageCodes = ['P-1', 'P-2', 'P-3'];
        $storageCodes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        $locations = array_merge(
            array_map(
                static fn (string $code): array => ['type' => 'portal', 'code' => $code, 'name' => 'Portal '.$code],
                $portalCodes,
            ),
            array_map(
                static fn (string $code): array => ['type' => 'garage', 'code' => $code, 'name' => 'Garaje '.$code],
                $garageCodes,
            ),
            array_map(
                static fn (string $code): array => ['type' => 'storage', 'code' => $code, 'name' => 'Trastero '.$code],
                $storageCodes,
            ),
        );

        foreach ($locations as $data) {
            Location::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
