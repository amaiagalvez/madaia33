<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\SupportedLocales;
use App\Models\VotingBallot;
use App\Models\VotingSelection;
use App\Models\PropertyAssignment;
use App\Http\Controllers\PublicVotingController;

trait HandlesVotingOwnerModals
{
    public function openCensus(int $votingId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $voting = Voting::query()->with('locations.location')->findOrFail($votingId);
        abort_unless($this->canAccessVoting($voting), 403);

        $owners = $this->eligibilityService->eligibleOwnersAtVotingDate($voting);

        $ballots = VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->with('selections.option')
            ->get();

        $votedOwnerIds = $ballots->pluck('owner_id')->flip()->all();

        $votesByOwner = $ballots->mapWithKeys(function (VotingBallot $ballot): array {
            $label = $this->formatBallotOptionLabel($ballot);

            return [$ballot->owner_id => $label];
        })->all();

        $this->ownersModalIsAnonymous = (bool) $voting->is_anonymous;
        $this->ownersModalContext = 'census';

        $this->ownersModalRows = $owners
            ->map(fn (Owner $owner): array => [
                'name' => $this->canSeeOwnerNamesInVotingModals() ? $owner->coprop1_name : '—',
                'has_voted' => isset($votedOwnerIds[$owner->id]),
                'vote' => $votesByOwner[$owner->id] ?? '',
                'properties' => $owner->activeAssignments
                    ->map(fn (PropertyAssignment $assignment): string => trim($assignment->property->location->code . ' ' . $assignment->property->name))
                    ->filter()
                    ->join(', '),
                'percentage' => $this->eligibilityService->percentageForOwnerAtVotingDate($voting, $owner),
                'delegated_by' => '—',
                'delegate_dni' => '—',
            ])
            ->values()
            ->all();

        $this->ownersModalTitle = __('votings.admin.census_modal_title', ['name' => $voting->name]);
        $this->showOwnersModal = true;
    }

    public function openVoters(int $votingId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $voting = Voting::query()->with('locations.location')->findOrFail($votingId);
        abort_unless($this->canAccessVoting($voting), 403);

        $owners = $this->eligibilityService->eligibleOwnersAtVotingDate($voting)->keyBy('id');

        $ballots = VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->with(['owner', 'castByUser', 'selections.option'])
            ->orderBy('voted_at')
            ->get();

        $this->ownersModalIsAnonymous = (bool) $voting->is_anonymous;
        $this->ownersModalContext = 'voters';

        $this->ownersModalRows = $ballots
            ->map(function (VotingBallot $ballot) use ($owners, $voting): ?array {
                $owner = $owners->get($ballot->owner_id);

                if (! $owner instanceof Owner) {
                    $owner = $ballot->owner;
                }

                if (! $owner instanceof Owner) {
                    return null;
                }

                return $this->ownerModalRowAtVotingDate($owner, $voting, $ballot);
            })
            ->filter()
            ->values()
            ->all();

        $this->ownersModalTitle = __('votings.admin.voters_modal_title', ['name' => $voting->name]);
        $this->showOwnersModal = true;
    }

    public function closeOwnersModal(): void
    {
        $this->showOwnersModal = false;
        $this->ownersModalIsAnonymous = false;
        $this->ownersModalContext = 'voters';
        $this->ownersModalTitle = '';
        $this->ownersModalRows = [];
    }

    private function formatBallotOptionName(VotingBallot $ballot): string
    {
        return $ballot->selections
            ->sortBy(static fn (VotingSelection $selection): int => $selection->option->position ?? PHP_INT_MAX)
            ->map(static function (VotingSelection $selection): string {
                $position = (int) $selection->option->position;

                return match (app()->getLocale()) {
                    'eu' => $position . '. aukera',
                    'es' => 'Opcion ' . $position,
                    default => 'Option ' . $position,
                };
            })
            ->filter()
            ->join(', ');
    }

    private function formatBallotOptionLabel(VotingBallot $ballot): string
    {
        return $ballot->selections
            ->sortBy(static fn (VotingSelection $selection): int => $selection->option->position ?? PHP_INT_MAX)
            ->map(static function (VotingSelection $selection): string {
                return trim((string) $selection->option->label);
            })
            ->filter()
            ->join(', ');
    }

    /**
     * @return array{name: string, percentage: float, vote: string, delegated_by: string, delegate_dni: string, has_voted: bool, properties: string}
     */
    private function ownerModalRowAtVotingDate(Owner $owner, Voting $voting, ?VotingBallot $ballot): array
    {
        $castByUser = $ballot?->castByUser;

        return [
            'name' => $this->canSeeOwnerNamesInVotingModals() ? $owner->coprop1_name : '—',
            'percentage' => $this->eligibilityService->percentageForOwnerAtVotingDate($voting, $owner),
            'vote' => $ballot instanceof VotingBallot ? $this->formatBallotOptionLabel($ballot) : '',
            'delegated_by' => $ballot instanceof VotingBallot
                ? ($castByUser instanceof User ? $castByUser->name : '—')
                : '—',
            'delegate_dni' => $ballot instanceof VotingBallot ? ($ballot->cast_delegate_dni ?? '—') : '—',
            'has_voted' => $ballot instanceof VotingBallot,
            'properties' => $owner->activeAssignments
                ->map(fn (PropertyAssignment $assignment): string => trim($assignment->property->location->code . ' ' . $assignment->property->name))
                ->filter()
                ->join(', '),
        ];
    }

    public function openDelegatedVoteModal(): void
    {
        abort_unless($this->currentUser()?->hasRole(Role::SUPER_ADMIN), 403);

        $this->openVoterModal('delegated');
    }

    public function updatedDelegatedSearch(): void
    {
        $this->applyVoterFilter('delegated');
    }

    public function closeDelegatedVoteModal(): void
    {
        $this->closeVoterModal('delegated');
    }

    public function startDelegatedVote(int $ownerId): void
    {
        $this->startVoterSession($ownerId, 'delegated');
    }

    public function openInPersonVoteModal(): void
    {
        abort_unless($this->currentUser()?->hasRole(Role::SUPER_ADMIN), 403);

        $this->openVoterModal('in_person');
    }

    public function updatedInPersonSearch(): void
    {
        $this->applyVoterFilter('in_person');
    }

    public function closeInPersonVoteModal(): void
    {
        $this->closeVoterModal('in_person');
    }

    public function startInPersonVote(int $ownerId): void
    {
        $this->startVoterSession($ownerId, 'in_person');
    }

    private function openVoterModal(string $type): void
    {
        abort_unless($this->canManageInPersonAndDelegatedSessions(), 403);

        $rows = $this->eligibilityService
            ->ownersWithPendingDelegations()
            ->map(static function (array $row): array {
                return [
                    'owner_id' => $row['owner']->id,
                    'owner_name' => $row['owner']->coprop1_name,
                    'owner_secondary_name' => (string) ($row['owner']->coprop2_name ?? ''),
                    'pending_votings' => $row['pending_votings'],
                    'portal_codes' => $row['portal_codes'],
                    'local_codes' => $row['local_codes'],
                    'garage_codes' => $row['garage_codes'],
                    'search_index' => $row['search_index'],
                ];
            })
            ->values()
            ->all();

        if ($type === 'delegated') {
            $this->delegatedRows = $rows;
            $this->delegatedSearch = '';
            $this->applyVoterFilter('delegated');
            $this->showDelegatedModal = true;

            return;
        }

        $this->inPersonRows = $rows;
        $this->inPersonSearch = '';
        $this->applyVoterFilter('in_person');
        $this->showInPersonModal = true;
    }

    private function closeVoterModal(string $type): void
    {
        if ($type === 'delegated') {
            $this->showDelegatedModal = false;
            $this->delegatedSearch = '';
            $this->delegatedRows = [];
            $this->filteredDelegatedRows = [];

            return;
        }

        $this->showInPersonModal = false;
        $this->inPersonSearch = '';
        $this->inPersonRows = [];
        $this->filteredInPersonRows = [];
    }

    private function startVoterSession(int $ownerId, string $type): void
    {
        abort_unless($this->canManageInPersonAndDelegatedSessions(), 403);

        $allowedOwnerIds = collect($this->eligibilityService->ownersWithPendingDelegations())
            ->map(static fn (array $row): int => $row['owner']->id)
            ->all();

        abort_unless(in_array($ownerId, $allowedOwnerIds, true), 404);

        if ($type === 'delegated') {
            session()->forget(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY);
            session()->put(PublicVotingController::DELEGATED_OWNER_SESSION_KEY, $ownerId);
        } else {
            session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
            session()->put(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY, $ownerId);
        }

        $this->redirectRoute(SupportedLocales::routeName('votings'));
    }

    private function applyVoterFilter(string $type): void
    {
        $search = mb_strtolower(trim($type === 'delegated' ? $this->delegatedSearch : $this->inPersonSearch));
        $rows = $type === 'delegated' ? $this->delegatedRows : $this->inPersonRows;

        if ($search === '') {
            if ($type === 'delegated') {
                $this->filteredDelegatedRows = $rows;
            } else {
                $this->filteredInPersonRows = $rows;
            }

            return;
        }

        $filtered = array_values(array_filter(
            $rows,
            static fn (array $row): bool => str_contains($row['search_index'], $search)
        ));

        if ($type === 'delegated') {
            $this->filteredDelegatedRows = $filtered;
        } else {
            $this->filteredInPersonRows = $filtered;
        }
    }
}
