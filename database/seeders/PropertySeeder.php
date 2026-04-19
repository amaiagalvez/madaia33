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
                $communityPct = $this->communityPctForType($location->type);
                $locationPct = $this->locationPctForType($location->type);

                foreach ($propertyNames as $propertyName) {
                    Property::withTrashed()->firstOrCreate(
                        [
                            'location_id' => $location->id,
                            'name' => $propertyName,
                        ],
                        [
                            'community_pct' => $communityPct,
                            'location_pct' => $locationPct,
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
                static fn (int $number): string => (string) $number,
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

    /**
     * community_pct per property by location type.
     * Portals/locals: ~90% community total distributed across 252 units.
     * Garages: ~10% community total distributed across 540 spaces.
     */
    private function communityPctForType(string $type): float
    {
        return match ($type) {
            'garage' => 0.02,
            default => 0.36,
        };
    }

    /**
     * location_pct per property by location type.
     * Portals/locals: 18 properties per location → 100/18.
     * Garages: 180 spaces per garage → 100/180.
     */
    private function locationPctForType(string $type): float
    {
        return match ($type) {
            'garage' => 0.56,
            default => 5.56,
        };
    }
}
