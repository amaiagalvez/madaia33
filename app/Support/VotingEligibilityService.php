<?php

namespace App\Support;

use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class VotingEligibilityService
{
    /**
     * @return Builder<Owner>
     */
    public function eligibleOwnersQuery(Voting $voting): Builder
    {
        [$residentialIds, $garageIds] = $this->allowedLocationIds($voting);

        return Owner::query()
            ->with(['user', 'activeAssignments.property.location'])
            ->whereHas('activeAssignments', function (Builder $assignmentQuery) use ($residentialIds, $garageIds): void {
                $assignmentQuery
                    ->whereNull('end_date')
                    ->when($residentialIds !== [] || $garageIds !== [], function (Builder $filteredQuery) use ($residentialIds, $garageIds): void {
                        $filteredQuery->whereHas('property.location', function (Builder $locationQuery) use ($residentialIds, $garageIds): void {
                            $locationQuery->where(function (Builder $nestedQuery) use ($residentialIds, $garageIds): void {
                                if ($residentialIds !== []) {
                                    $nestedQuery->orWhere(function (Builder $residentialQuery) use ($residentialIds): void {
                                        $residentialQuery
                                            ->whereIn('type', ['portal', 'local'])
                                            ->whereIn('id', $residentialIds);
                                    });
                                }

                                if ($garageIds !== []) {
                                    $nestedQuery->orWhere(function (Builder $garageQuery) use ($garageIds): void {
                                        $garageQuery
                                            ->where('type', 'garage')
                                            ->whereIn('id', $garageIds);
                                    });
                                }
                            });
                        });
                    });
            });
    }

    /**
     * @return Collection<int, Owner>
     */
    public function eligibleOwners(Voting $voting): Collection
    {
        return $this->eligibleOwnersQuery($voting)
            ->orderBy('coprop1_name')
            ->get();
    }

    public function ownerCanVote(Voting $voting, Owner $owner): bool
    {
        $owner->loadMissing('activeAssignments.property.location');

        return $this->eligibleAssignments($voting, $owner)->isNotEmpty();
    }

    public function percentageForOwner(Voting $voting, Owner $owner): float
    {
        return (float) $this->eligibleAssignments($voting, $owner)
            ->sum(fn(PropertyAssignment $assignment): float => (float) ($assignment->property->community_pct ?? 0));
    }

    /**
     * @return Collection<int, Voting>
     */
    public function openEligibleVotingsForOwner(Owner $owner): Collection
    {
        $owner->loadMissing('activeAssignments.property.location');

        return Voting::query()
            ->with(['options', 'locations.location', 'optionTotals.option'])
            ->publishedOpen()
            ->orderBy('starts_at')
            ->get()
            ->filter(fn(Voting $voting): bool => $this->ownerCanVote($voting, $owner));
    }

    /**
     * @return Collection<int, array{owner: Owner, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public function ownersWithPendingDelegations(): Collection
    {
        $openVotings = Voting::query()
            ->with(['locations.location'])
            ->publishedOpen()
            ->get();

        if ($openVotings->isEmpty()) {
            return collect();
        }

        $ballotsByOwner = VotingBallot::query()
            ->whereIn('voting_id', $openVotings->pluck('id'))
            ->get(['owner_id', 'voting_id'])
            ->groupBy('owner_id')
            ->map(fn(Collection $ballots): Collection => $ballots->pluck('voting_id'));

        $eligibleOwnerIdsByVoting = $openVotings
            ->mapWithKeys(function (Voting $voting): array {
                return [
                    $voting->id => $this->eligibleOwnersQuery($voting)
                        ->pluck('owners.id')
                        ->map(static fn($ownerId): int => (int) $ownerId)
                        ->all(),
                ];
            });

        $pendingByOwner = [];

        foreach ($openVotings as $voting) {
            $eligibleOwnerIds = $eligibleOwnerIdsByVoting->get($voting->id, []);

            foreach ($eligibleOwnerIds as $ownerId) {
                if ($ballotsByOwner->get($ownerId, collect())->contains($voting->id)) {
                    continue;
                }

                $pendingByOwner[$ownerId] = ($pendingByOwner[$ownerId] ?? 0) + 1;
            }
        }

        if ($pendingByOwner === []) {
            return collect();
        }

        return Owner::query()
            ->whereIn('id', array_keys($pendingByOwner))
            ->with('activeAssignments.property.location')
            ->orderBy('coprop1_name')
            ->get()
            ->map(function (Owner $owner) use ($pendingByOwner): array {
                $portalCodes = $owner->activeAssignments
                    ->pluck('property.location')
                    ->filter(static fn($location): bool => $location !== null && $location->type === 'portal')
                    ->pluck('code')
                    ->map(static fn($code): string => (string) $code)
                    ->unique()
                    ->sort()
                    ->values();

                $garageCodes = $owner->activeAssignments
                    ->pluck('property.location')
                    ->filter(static fn($location): bool => $location !== null && $location->type === 'garage')
                    ->pluck('code')
                    ->map(static fn($code): string => (string) $code)
                    ->unique()
                    ->sort()
                    ->values();

                $localCodes = $owner->activeAssignments
                    ->pluck('property.location')
                    ->filter(static fn($location): bool => $location !== null && $location->type === 'local')
                    ->pluck('code')
                    ->map(static fn($code): string => (string) $code)
                    ->unique()
                    ->sort()
                    ->values();

                $searchTerms = [
                    $owner->coprop1_name,
                    $owner->coprop1_dni,
                    $owner->coprop1_email,
                    $owner->coprop1_phone,
                    $owner->coprop2_name,
                    $owner->coprop2_dni,
                    $owner->coprop2_email,
                    $owner->coprop2_phone,
                    $portalCodes->implode(' '),
                    $localCodes->implode(' '),
                    $garageCodes->implode(' '),
                ];

                return [
                    'owner' => $owner,
                    'pending_votings' => $pendingByOwner[$owner->id] ?? 0,
                    'portal_codes' => $portalCodes->implode(', '),
                    'local_codes' => $localCodes->implode(', '),
                    'garage_codes' => $garageCodes->implode(', '),
                    'search_index' => mb_strtolower(implode(' ', array_filter($searchTerms))),
                ];
            })
            ->values();
    }

    /**
     * @return array{0: array<int, int>, 1: array<int, int>}
     */
    public function allowedLocationIds(Voting $voting): array
    {
        $voting->loadMissing('locations.location');

        $residentialIds = $voting->locations
            ->filter(fn($votingLocation): bool => in_array($votingLocation->location?->type, ['portal', 'local'], true))
            ->pluck('location_id')
            ->all();

        $garageIds = $voting->locations
            ->filter(fn($votingLocation): bool => $votingLocation->location?->type === 'garage')
            ->pluck('location_id')
            ->all();

        return [$residentialIds, $garageIds];
    }

    /**
     * @return Collection<int, PropertyAssignment>
     */
    private function eligibleAssignments(Voting $voting, Owner $owner): Collection
    {
        [$residentialIds, $garageIds] = $this->allowedLocationIds($voting);

        return $owner->activeAssignments
            ->filter(function (PropertyAssignment $assignment) use ($residentialIds, $garageIds): bool {
                $location = $assignment->property?->location;

                if ($location === null) {
                    return false;
                }

                if ($residentialIds === [] && $garageIds === []) {
                    return true;
                }

                if (in_array($location->type, ['portal', 'local'], true) && in_array($location->id, $residentialIds, true)) {
                    return true;
                }

                return $location->type === 'garage' && in_array($location->id, $garageIds, true);
            })
            ->values();
    }
}
