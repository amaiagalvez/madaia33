<?php

namespace App\Actions\Votings;

use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
use App\Support\VotingEligibilityService;

class BuildVotingResultsTableAction
{
    public function __construct(
        private readonly VotingEligibilityService $eligibilityService,
    ) {}

    /**
     * @return array{rows: array<int, array{owner_name: string, properties: string, owner_percentage: float, selected_option_id: ?int}>, options: array<int, array{id: int, label: string, votes_count: int, pct_total: float, total_percentage: float, expected_votes_count: int, expected_pct_total: float, has_votes_count_mismatch: bool, has_pct_total_mismatch: bool}>, charts: array{participation: array{eligible_owners: int, voted_owners: int, eligible_pct_total: float, voted_pct_total: float}, options: array{owners: array<int, array{id: int, label: string, value: int}>, percentages: array<int, array{id: int, label: string, value: float}>}}, is_anonymous: bool}
     */
    public function execute(Voting $voting, bool $canSeeOwnerNames): array
    {
        $voting->loadMissing(['options', 'optionTotals']);

        $ballots = $this->uniqueBallotsForVoting($voting);
        $ownersById = $this->eligibilityService
            ->eligibleOwnersAtVotingDate($voting)
            ->keyBy('id');
        $selectedOptionIdsByOwner = $this->selectedOptionIdsByOwner($ballots);
        $votedOwnerPercentagesById = $this->ownerPercentagesById($voting, $ballots, $ownersById);
        $eligibleOwnerPercentagesById = $this->eligibleOwnerPercentagesById($voting, $ownersById);
        $options = $this->buildOptions($voting, $selectedOptionIdsByOwner, $votedOwnerPercentagesById);

        return [
            'rows' => $this->buildRows(
                $voting,
                $ballots,
                $ownersById,
                $canSeeOwnerNames,
                $selectedOptionIdsByOwner,
            ),
            'options' => $options,
            'charts' => [
                'participation' => $this->buildParticipationChart($eligibleOwnerPercentagesById, $votedOwnerPercentagesById),
                'options' => $this->buildOptionCharts($options),
            ],
            'is_anonymous' => (bool) $voting->is_anonymous,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, VotingBallot>
     */
    private function uniqueBallotsForVoting(Voting $voting)
    {
        return VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->with(['owner', 'selections'])
            ->orderBy('voted_at')
            ->get()
            ->unique('owner_id')
            ->values();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, VotingBallot>  $ballots
     * @return array<int, ?int>
     */
    private function selectedOptionIdsByOwner($ballots): array
    {
        return $ballots
            ->mapWithKeys(static function (VotingBallot $ballot): array {
                $selection = $ballot->selections
                    ->sortBy(static fn (VotingSelection $item): int => $item->id)
                    ->first();

                return [
                    $ballot->owner_id => $selection instanceof VotingSelection
                        ? (int) $selection->voting_option_id
                        : null,
                ];
            })
            ->all();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, VotingBallot>  $ballots
     * @param  Collection<int, Owner>  $ownersById
     * @param  array<int, ?int>  $selectedOptionIdsByOwner
     * @return array<int, array{owner_name: string, properties: string, owner_percentage: float, selected_option_id: ?int}>
     */
    private function buildRows(
        Voting $voting,
        $ballots,
        $ownersById,
        bool $canSeeOwnerNames,
        array $selectedOptionIdsByOwner,
    ): array {
        return $ballots
            ->map(function (VotingBallot $ballot) use ($ownersById, $voting, $canSeeOwnerNames, $selectedOptionIdsByOwner): ?array {
                $owner = $ownersById->get($ballot->owner_id);

                if (! $owner instanceof Owner) {
                    $owner = $ballot->owner;
                }

                if (! $owner instanceof Owner) {
                    return null;
                }

                $owner->loadMissing('activeAssignments.property.location');

                return [
                    'owner_name' => $canSeeOwnerNames ? $owner->fullName1 : '—',
                    'properties' => $this->ownerPropertiesLabel($owner),
                    'owner_percentage' => (float) $this->eligibilityService->percentageForOwnerAtVotingDate($voting, $owner),
                    'selected_option_id' => $voting->is_anonymous
                        ? null
                        : (($selectedOptionIdsByOwner[$owner->id] ?? null) !== null
                            ? (int) $selectedOptionIdsByOwner[$owner->id]
                            : null),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, VotingBallot>  $ballots
     * @param  Collection<int, Owner>  $ownersById
     * @return array<int, float>
     */
    private function ownerPercentagesById(Voting $voting, $ballots, $ownersById): array
    {
        return $ballots
            ->mapWithKeys(function (VotingBallot $ballot) use ($voting, $ownersById): array {
                $owner = $ownersById->get($ballot->owner_id);

                if (! $owner instanceof Owner) {
                    $owner = $ballot->owner;
                }

                if (! $owner instanceof Owner) {
                    return [];
                }

                return [
                    $owner->id => (float) $this->eligibilityService->percentageForOwnerAtVotingDate($voting, $owner),
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, Owner>  $ownersById
     * @return array<int, float>
     */
    private function eligibleOwnerPercentagesById(Voting $voting, $ownersById): array
    {
        return $ownersById
            ->mapWithKeys(function (Owner $owner) use ($voting): array {
                return [
                    $owner->id => (float) $this->eligibilityService->percentageForOwnerAtVotingDate($voting, $owner),
                ];
            })
            ->all();
    }

    private function ownerPropertiesLabel(Owner $owner): string
    {
        return $owner->activeAssignments
            ->map(function (PropertyAssignment $assignment): string {
                $propertyLabel = trim(sprintf(
                    '[%s] %s %s',
                    $assignment->property->displayCode(),
                    $assignment->property->location->name,
                    $assignment->property->name
                ));
                $pct = (float) ($assignment->property->community_pct ?? 0);

                return sprintf('%s (%s%%)', $propertyLabel, number_format($pct, 2, ',', '.'));
            })
            ->filter()
            ->join(', ');
    }

    /**
     * @param  array<int, ?int>  $selectedOptionIdsByOwner
     * @param  array<int, float>  $ownerPercentagesById
     * @return array<int, array{id: int, label: string, votes_count: int, pct_total: float, total_percentage: float, expected_votes_count: int, expected_pct_total: float, has_votes_count_mismatch: bool, has_pct_total_mismatch: bool}>
     */
    private function buildOptions(Voting $voting, array $selectedOptionIdsByOwner, array $ownerPercentagesById): array
    {
        $totalsByOptionId = $voting->optionTotals->keyBy('voting_option_id');

        return $voting->options
            ->map(function (VotingOption $option) use ($totalsByOptionId, $selectedOptionIdsByOwner, $ownerPercentagesById): array {
                /** @var VotingOptionTotal|null $total */
                $total = $totalsByOptionId->get($option->id);
                $votesCount = $total instanceof VotingOptionTotal ? (int) $total->votes_count : 0;
                $pctTotal = $total instanceof VotingOptionTotal ? (float) $total->pct_total : 0.0;
                $expectedVotesCount = 0;
                $expectedPctTotal = 0.0;

                foreach ($selectedOptionIdsByOwner as $ownerId => $selectedOptionId) {
                    if ((int) $selectedOptionId !== (int) $option->id) {
                        continue;
                    }

                    $expectedVotesCount++;
                    $expectedPctTotal += (float) ($ownerPercentagesById[(int) $ownerId] ?? 0.0);
                }

                return [
                    'id' => $option->id,
                    'label' => $option->label,
                    'votes_count' => $votesCount,
                    'pct_total' => $pctTotal,
                    'total_percentage' => $pctTotal,
                    'expected_votes_count' => $expectedVotesCount,
                    'expected_pct_total' => $expectedPctTotal,
                    'has_votes_count_mismatch' => $votesCount !== $expectedVotesCount,
                    'has_pct_total_mismatch' => round($pctTotal, 2) !== round($expectedPctTotal, 2),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, float>  $eligibleOwnerPercentagesById
     * @param  array<int, float>  $votedOwnerPercentagesById
     * @return array{eligible_owners: int, voted_owners: int, eligible_pct_total: float, voted_pct_total: float}
     */
    private function buildParticipationChart(array $eligibleOwnerPercentagesById, array $votedOwnerPercentagesById): array
    {
        return [
            'eligible_owners' => count($eligibleOwnerPercentagesById),
            'voted_owners' => count($votedOwnerPercentagesById),
            'eligible_pct_total' => (float) array_sum($eligibleOwnerPercentagesById),
            'voted_pct_total' => (float) array_sum($votedOwnerPercentagesById),
        ];
    }

    /**
     * @param  array<int, array{id: int, label: string, votes_count: int, pct_total: float, total_percentage: float, expected_votes_count: int, expected_pct_total: float, has_votes_count_mismatch: bool, has_pct_total_mismatch: bool}>  $options
     * @return array{owners: array<int, array{id: int, label: string, value: int}>, percentages: array<int, array{id: int, label: string, value: float}>}
     */
    private function buildOptionCharts(array $options): array
    {
        return [
            'owners' => collect($options)
                ->map(static function (array $option): array {
                    return [
                        'id' => (int) $option['id'],
                        'label' => (string) $option['label'],
                        'value' => (int) $option['votes_count'],
                    ];
                })
                ->values()
                ->all(),
            'percentages' => collect($options)
                ->map(static function (array $option): array {
                    return [
                        'id' => (int) $option['id'],
                        'label' => (string) $option['label'],
                        'value' => (float) $option['pct_total'],
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
