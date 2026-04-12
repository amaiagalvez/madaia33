<?php

namespace App\Support;

use App\Models\Voting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Calculates eligible voter census counts for votings.
 *
 * Computes how many active owners are eligible to vote in each voting,
 * based on their property assignments and voting's configured locations.
 */
class VotingCensusCalculator
{
    public function __construct(
        private readonly VotingEligibilityService $eligibilityService,
    ) {}

    /**
     * Calculate eligible owner counts for paginated votings.
     *
     * @param  LengthAwarePaginator<int, Voting>  $votings
     * @return array<int, int>
     */
    public function calculate(LengthAwarePaginator $votings): array
    {
        $ownerLocations = $this->buildOwnerLocationMap();

        return $votings->getCollection()
            ->mapWithKeys(function (Voting $voting) use ($ownerLocations): array {
                [$residentialIds, $garageIds] = $this->eligibilityService->allowedLocationIds($voting);
                $residentialIds = array_map('intval', $residentialIds);
                $garageIds = array_map('intval', $garageIds);

                $eligibleOwnersCount = $ownerLocations->filter(static function (array $locations) use ($residentialIds, $garageIds): bool {
                    if ($residentialIds === [] && $garageIds === []) {
                        return true;
                    }

                    if ($residentialIds !== [] && array_intersect($locations['residential_ids'], $residentialIds) !== []) {
                        return true;
                    }

                    return $garageIds !== [] && array_intersect($locations['garage_ids'], $garageIds) !== [];
                })->count();

                return [
                    $voting->id => $eligibleOwnersCount,
                ];
            })
            ->all();
    }

    /**
     * Build map of owner -> eligible location IDs.
     *
     * Groups active property assignments by owner and location type.
     *
     * @return Collection<int, array{residential_ids: array<int, int>, garage_ids: array<int, int>}>
     */
    private function buildOwnerLocationMap(): Collection
    {
        $assignmentRows = DB::table('property_assignments as assignments')
            ->join('properties as properties', 'properties.id', '=', 'assignments.property_id')
            ->join('locations as locations', 'locations.id', '=', 'properties.location_id')
            ->whereNull('assignments.end_date')
            ->whereIn('locations.type', ['portal', 'local', 'garage'])
            ->select([
                'assignments.owner_id as owner_id',
                'locations.id as location_id',
                'locations.type as location_type',
            ])
            ->distinct()
            ->get();

        return $assignmentRows
            ->groupBy('owner_id')
            ->mapWithKeys(static function ($rows, int|string $ownerId): array {
                return [
                    (int) $ownerId => [
                        'residential_ids' => $rows
                            ->filter(static fn ($row): bool => in_array($row->location_type, ['portal', 'local'], true))
                            ->pluck('location_id')
                            ->map(static fn ($id): int => (int) $id)
                            ->values()
                            ->all(),
                        'garage_ids' => $rows
                            ->where('location_type', 'garage')
                            ->pluck('location_id')
                            ->map(static fn ($id): int => (int) $id)
                            ->values()
                            ->all(),
                    ],
                ];
            });
    }
}
