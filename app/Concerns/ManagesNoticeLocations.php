<?php

namespace App\Concerns;

use App\Models\Role;
use App\Models\Notice;
use App\Models\Location;
use App\Models\NoticeLocation;

trait ManagesNoticeLocations
{
    private function syncLocations(Notice $notice): void
    {
        $notice->locations()->delete();

        $rows = [];

        foreach (array_values(array_unique($this->selectedLocations)) as $selected) {
            $locationId = $this->resolveSelectionToForeignKey((string) $selected);

            if ($locationId === null) {
                continue;
            }

            $rows[] = [
                'notice_id' => $notice->id,
                'location_id' => $locationId,
            ];
        }

        if ($rows !== []) {
            NoticeLocation::insert($rows);
        }
    }

    private function resolveSelectionToForeignKey(string $selected): ?int
    {
        return Location::query()
            ->where('code', $selected)
            ->value('id');
    }

    /**
     * @return array<int, array{code: string, type: string, label: string}>
     */
    private function allLocationOptions(): array
    {
        $user = $this->currentUser();

        $query = Location::query()->whereIn('type', ['portal', 'local', 'garage']);

        if ($user?->hasRole(Role::GENERAL_ADMIN)) {
            return [];
        }

        if ($user?->hasRole(Role::COMMUNITY_ADMIN)) {
            $query->whereIn('id', $user->managedLocations()->pluck('locations.id'));
        }

        $locations = $query
            ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 WHEN type = 'garage' THEN 3 ELSE 4 END")
            ->orderBy('code')
            ->get();

        return $locations
            ->map(fn (Location $location): array => [
                'code' => $location->code,
                'type' => $location->type,
                'label' => $this->locationLabel($location) . $location->code,
            ])
            ->all();
    }

    private function locationLabel(Location $location): string
    {
        return match ($location->type) {
            'portal' => __('admin.locations.types.portal') . ' ',
            'local' => __('admin.locations.types.local') . ' ',
            'garage' => __('admin.locations.types.garage') . ' ',
            default => '',
        };
    }

    /**
     * @return array<int, string>
     */
    private function allowedLocationCodes(): array
    {
        return collect($this->allLocationOptions())
            ->map(static fn (array $location): string => $location['code'])
            ->values()
            ->all();
    }
}
