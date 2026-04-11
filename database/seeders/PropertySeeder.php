<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        Location::query()
            ->whereIn('type', ['portal', 'local', 'garage'])
            ->get(['id', 'type'])
            ->each(function (Location $location): void {
                $propertyNames = $this->propertyNamesForType($location->type);

                foreach ($propertyNames as $propertyName) {
                    Property::withTrashed()->updateOrCreate(
                        [
                            'location_id' => $location->id,
                            'name' => $propertyName,
                        ],
                        [
                            'community_pct' => null,
                            'location_pct' => null,
                            'deleted_at' => null,
                        ],
                    );
                }
            });
    }

    /**
     * @return array<int, string>
     */
    private function propertyNamesForType(string $type): array
    {
        if ($type === 'garage') {
            return array_map(
                static fn(int $number): string => (string) $number,
                range(1, 180),
            );
        }

        $portalProperties = [];

        foreach (range(1, 6) as $floor) {
            foreach (['A', 'B', 'C'] as $letter) {
                $portalProperties[] = $floor . '-' . $letter;
            }
        }

        return $portalProperties;
    }
}
