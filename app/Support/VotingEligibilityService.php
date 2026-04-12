<?php

namespace App\Support;

use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use Illuminate\Support\Carbon;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return Collection<int, Owner>
     */
    public function eligibleOwnersAtVotingDate(Voting $voting): Collection
    {
        $referenceDate = $this->votingReferenceDate($voting);

        return $this->eligibleOwnersQueryAtDate($voting, $referenceDate)
            ->orderBy('coprop1_name')
            ->get()
            ->each(static function (Owner $owner): void {
                $owner->setRelation('activeAssignments', $owner->assignments);
            });
    }

    public function ownerCanVote(Voting $voting, Owner $owner): bool
    {
        $owner->loadMissing('activeAssignments.property.location');

        return $this->eligibleAssignments($voting, $owner)->isNotEmpty();
    }

    public function percentageForOwner(Voting $voting, Owner $owner): float
    {
        return (float) $this->eligibleAssignments($voting, $owner)
            ->sum(fn (PropertyAssignment $assignment): float => (float) ($assignment->property->community_pct ?? 0));
    }

    public function percentageForOwnerAtVotingDate(Voting $voting, Owner $owner): float
    {
        return (float) $this->eligibleAssignmentsAtDate($voting, $owner, $this->votingReferenceDate($voting))
            ->sum(fn (PropertyAssignment $assignment): float => (float) ($assignment->property->community_pct ?? 0));
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
            ->filter(fn (Voting $voting): bool => $this->ownerCanVote($voting, $owner));
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

        $ballotsByOwner = $this->ballotsByOwner($openVotings);
        $eligibleOwnerIdsByVoting = $this->eligibleOwnerIdsByVoting($openVotings);
        $pendingByOwner = $this->pendingVotingsByOwner($openVotings, $ballotsByOwner, $eligibleOwnerIdsByVoting);

        if ($pendingByOwner === []) {
            return collect();
        }

        return Owner::query()
            ->whereIn('id', array_keys($pendingByOwner))
            ->with('activeAssignments.property.location')
            ->orderBy('coprop1_name')
            ->get()
            ->map(fn (Owner $owner): array => $this->delegationSummary($owner, $pendingByOwner))
            ->values();
    }

    /**
     * @param  Collection<int, Voting>  $openVotings
     * @return array<int, array<int, int>>
     */
    private function ballotsByOwner(Collection $openVotings): array
    {
        return VotingBallot::query()
            ->whereIn('voting_id', $openVotings->pluck('id'))
            ->get(['owner_id', 'voting_id'])
            ->groupBy('owner_id')
            ->map(fn (Collection $ballots): array => $ballots
                ->pluck('voting_id')
                ->map(static fn ($votingId): int => (int) $votingId)
                ->values()
                ->all())
            ->all();
    }

    /**
     * @param  Collection<int, Voting>  $openVotings
     * @return array<int, array<int, int>>
     */
    private function eligibleOwnerIdsByVoting(Collection $openVotings): array
    {
        return $openVotings->mapWithKeys(function (Voting $voting): array {
            return [
                $voting->id => $this->eligibleOwnersQuery($voting)
                    ->pluck('owners.id')
                    ->map(static fn ($ownerId): int => (int) $ownerId)
                    ->all(),
            ];
        })->all();
    }

    /**
     * @param  Collection<int, Voting>  $openVotings
     * @param  array<int, array<int, int>>  $ballotsByOwner
     * @param  array<int, array<int, int>>  $eligibleOwnerIdsByVoting
     * @return array<int, int>
     */
    private function pendingVotingsByOwner(Collection $openVotings, array $ballotsByOwner, array $eligibleOwnerIdsByVoting): array
    {
        $pendingByOwner = [];

        foreach ($openVotings as $voting) {
            foreach ($eligibleOwnerIdsByVoting[$voting->id] ?? [] as $ownerId) {
                if (in_array($voting->id, $ballotsByOwner[$ownerId] ?? [], true)) {
                    continue;
                }

                $pendingByOwner[$ownerId] = ($pendingByOwner[$ownerId] ?? 0) + 1;
            }
        }

        return $pendingByOwner;
    }

    /**
     * @param  array<int, int>  $pendingByOwner
     * @return array{owner: Owner, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}
     */
    private function delegationSummary(Owner $owner, array $pendingByOwner): array
    {
        $portalCodes = $this->ownerLocationCodes($owner, 'portal');
        $localCodes = $this->ownerLocationCodes($owner, 'local');
        $garageCodes = $this->ownerLocationCodes($owner, 'garage');

        return [
            'owner' => $owner,
            'pending_votings' => $pendingByOwner[$owner->id] ?? 0,
            'portal_codes' => $portalCodes->implode(', '),
            'local_codes' => $localCodes->implode(', '),
            'garage_codes' => $garageCodes->implode(', '),
            'search_index' => $this->delegationSearchIndex($owner, $portalCodes, $localCodes, $garageCodes),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function ownerLocationCodes(Owner $owner, string $type): Collection
    {
        return $owner->activeAssignments
            ->filter(static fn (PropertyAssignment $assignment): bool => $assignment->property?->location?->type === $type)
            ->map(fn (PropertyAssignment $assignment): string => $this->propertyLabelWithCommunityPct($assignment))
            ->unique()
            ->sort()
            ->values();
    }

    private function propertyLabelWithCommunityPct(PropertyAssignment $assignment): string
    {
        $code = (string) ($assignment->property?->location?->code ?? '');
        $name = (string) ($assignment->property?->name ?? '');
        $communityPct = $assignment->property?->community_pct;

        $label = trim($code . ' ' . $name);

        if ($communityPct === null) {
            return $label;
        }

        return trim($label . ' (' . number_format((float) $communityPct, 2, ',', '.') . '%)');
    }

    /**
     * @param  Collection<int, string>  $portalCodes
     * @param  Collection<int, string>  $localCodes
     * @param  Collection<int, string>  $garageCodes
     */
    private function delegationSearchIndex(Owner $owner, Collection $portalCodes, Collection $localCodes, Collection $garageCodes): string
    {
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

        return mb_strtolower(implode(' ', array_filter($searchTerms)));
    }

    /**
     * @return Builder<Owner>
     */
    private function eligibleOwnersQueryAtDate(Voting $voting, Carbon $referenceDate): Builder
    {
        [$residentialIds, $garageIds] = $this->allowedLocationIds($voting);

        return Owner::query()
            ->with([
                'user',
                'assignments' => function (HasMany $assignmentQuery) use ($referenceDate, $residentialIds, $garageIds): void {
                    $this->applyEligibleAssignmentQueryConstraints($assignmentQuery, $referenceDate, $residentialIds, $garageIds);
                },
            ])
            ->whereHas('assignments', function (Builder $assignmentQuery) use ($referenceDate, $residentialIds, $garageIds): void {
                $this->applyEligibleAssignmentQueryConstraints($assignmentQuery, $referenceDate, $residentialIds, $garageIds);
            });
    }

    /**
     * @return array{0: array<int, int>, 1: array<int, int>}
     */
    public function allowedLocationIds(Voting $voting): array
    {
        $voting->loadMissing('locations.location');

        $residentialIds = $voting->locations
            ->filter(fn ($votingLocation): bool => in_array($votingLocation->location?->type, ['portal', 'local'], true))
            ->pluck('location_id')
            ->all();

        $garageIds = $voting->locations
            ->filter(fn ($votingLocation): bool => $votingLocation->location?->type === 'garage')
            ->pluck('location_id')
            ->all();

        return [$residentialIds, $garageIds];
    }

    private function votingReferenceDate(Voting $voting): Carbon
    {
        return $voting->starts_at instanceof Carbon
            ? $voting->starts_at->copy()
            : Carbon::parse($voting->starts_at ?? today());
    }

    /**
     * @param  array<int, int>  $residentialIds
     * @param  array<int, int>  $garageIds
     */
    private function applyEligibleAssignmentQueryConstraints(Builder|HasMany $assignmentQuery, Carbon $referenceDate, array $residentialIds, array $garageIds): void
    {
        $assignmentQuery
            ->with('property.location')
            ->whereDate('start_date', '<=', $referenceDate)
            ->where(function (Builder $dateQuery) use ($referenceDate): void {
                $dateQuery
                    ->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $referenceDate);
            })
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

    /**
     * @return Collection<int, PropertyAssignment>
     */
    private function eligibleAssignmentsAtDate(Voting $voting, Owner $owner, Carbon $referenceDate): Collection
    {
        [$residentialIds, $garageIds] = $this->allowedLocationIds($voting);

        if (! $owner->relationLoaded('assignments')) {
            $owner->load([
                'assignments' => function (Builder $assignmentQuery) use ($referenceDate, $residentialIds, $garageIds): void {
                    $this->applyEligibleAssignmentQueryConstraints($assignmentQuery, $referenceDate, $residentialIds, $garageIds);
                },
            ]);
        }

        return $owner->assignments
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
