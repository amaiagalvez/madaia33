<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Livewire\Component;
use App\Models\Location;
use App\SupportedLocales;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use Livewire\WithPagination;
use App\Models\VotingLocation;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Support\VotingCensusCalculator;
use App\Support\VotingEligibilityService;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Http\Controllers\PublicVotingController;

class Votings extends Component
{
    use BuildsLocaleFieldConfigs;
    use WithPagination;

    private VotingEligibilityService $eligibilityService;

    private VotingCensusCalculator $censusCalculator;

    public bool $showCreateForm = false;

    public ?int $editingVotingId = null;

    public int $editingVotingBallotCount = 0;

    public string $nameEu = '';

    public string $nameEs = '';

    public string $questionEu = '';

    public string $questionEs = '';

    public string $startsAt = '';

    public string $endsAt = '';

    public bool $isPublished = false;

    public bool $isAnonymous = false;

    /**
     * @var array<int, array{labelEu: string, labelEs: string}>
     */
    public array $options = [];

    /**
     * @var array<int, string>
     */
    public array $selectedLocations = [];

    public bool $showOwnersModal = false;

    public bool $ownersModalIsAnonymous = false;

    public bool $showDeleteModal = false;

    public ?int $confirmingDeleteVotingId = null;

    public string $ownersModalTitle = '';

    /**
     * @var array<int, array{name: string, has_voted: bool, vote: string, properties: string, percentage: float, delegated_by: string, delegate_dni: string}>
     */
    public array $ownersModalRows = [];

    public bool $showDelegatedModal = false;

    /**
     * @var array<int, array{owner_id: int, owner_name: string, owner_secondary_name: string, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public array $delegatedRows = [];

    /**
     * @var array<int, array{owner_id: int, owner_name: string, owner_secondary_name: string, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public array $filteredDelegatedRows = [];

    public string $delegatedSearch = '';

    public bool $showInPersonModal = false;

    /**
     * @var array<int, array{owner_id: int, owner_name: string, owner_secondary_name: string, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public array $inPersonRows = [];

    /**
     * @var array<int, array{owner_id: int, owner_name: string, owner_secondary_name: string, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public array $filteredInPersonRows = [];

    public string $inPersonSearch = '';

    public function boot(VotingEligibilityService $eligibilityService): void
    {
        $this->eligibilityService = $eligibilityService;
        $this->censusCalculator = app(VotingCensusCalculator::class);
    }

    public function mount(): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $this->options = [$this->emptyOptionRow()];
    }

    public function createVoting(): void
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function editVoting(int $votingId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $voting = Voting::query()
            ->with(['options', 'locations'])
            ->findOrFail($votingId);

        $this->editingVotingId = $voting->id;
        $this->editingVotingBallotCount = $voting->ballots()->count();
        $this->nameEu = $voting->name_eu;
        $this->nameEs = (string) ($voting->name_es ?? '');
        $this->questionEu = $voting->question_eu;
        $this->questionEs = (string) ($voting->question_es ?? '');
        $this->startsAt = (string) $voting->starts_at?->format('Y-m-d');
        $this->endsAt = (string) $voting->ends_at?->format('Y-m-d');
        $this->isPublished = (bool) $voting->is_published;
        $this->isAnonymous = (bool) $voting->is_anonymous;
        $this->selectedLocations = $voting->locations
            ->map(static fn(VotingLocation $location): string => (string) $location->location_id)
            ->values()
            ->all();

        $this->options = $voting->options
            ->map(static function (VotingOption $option): array {
                return [
                    'labelEu' => $option->label_eu,
                    'labelEs' => (string) ($option->label_es ?? ''),
                ];
            })
            ->values()
            ->all();

        if ($this->options === []) {
            $this->options = [$this->emptyOptionRow()];
        }

        $this->showCreateForm = true;
    }

    public function addOption(): void
    {
        $this->options[] = $this->emptyOptionRow();
    }

    public function removeOption(int $index): void
    {
        if (count($this->options) === 1) {
            $this->options = [$this->emptyOptionRow()];

            return;
        }

        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function saveVoting(): void
    {
        $this->validateVotingForm();

        $normalizedOptions = $this->normalizedOptions();

        if ($normalizedOptions === []) {
            $this->addError('options.0.labelEu', __('votings.admin.validations.option_required'));

            return;
        }

        DB::transaction(fn() => $this->persistVoting($normalizedOptions));

        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', __('general.messages.saved'));
    }

    public function cancelVoting(): void
    {
        $this->resetForm();
        $this->showCreateForm = false;
    }

    public function confirmDeleteVoting(int $votingId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $this->confirmingDeleteVotingId = $votingId;
        $this->showDeleteModal = true;
    }

    public function cancelDeleteVoting(): void
    {
        $this->confirmingDeleteVotingId = null;
        $this->showDeleteModal = false;
    }

    public function deleteVoting(): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        if ($this->confirmingDeleteVotingId === null) {
            return;
        }

        $voting = Voting::query()
            ->withCount('ballots')
            ->findOrFail($this->confirmingDeleteVotingId);

        if ($voting->ballots_count > 0) {
            session()->flash('error', __('votings.admin.delete_blocked_with_votes'));
            $this->cancelDeleteVoting();

            return;
        }

        $voting->delete();

        if ($this->editingVotingId === $voting->id) {
            $this->resetForm();
            $this->showCreateForm = false;
        }

        session()->flash('message', __('general.messages.deleted'));
        $this->cancelDeleteVoting();
    }

    private function validateVotingForm(): void
    {
        $this->validate([
            'nameEu' => ['required', 'string', 'max:255'],
            'nameEs' => ['nullable', 'string', 'max:255'],
            'questionEu' => ['required', 'string'],
            'questionEs' => ['nullable', 'string'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['required', 'date', 'after_or_equal:startsAt'],
            'isPublished' => ['boolean'],
            'isAnonymous' => ['boolean'],
            'selectedLocations' => ['array'],
            'selectedLocations.*' => ['exists:locations,id'],
            'options' => ['required', 'array', 'min:1'],
            'options.*.labelEu' => ['nullable', 'string', 'max:255'],
            'options.*.labelEs' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @return array<int, array{label_eu: string, label_es: string}>
     */
    private function normalizedOptions(): array
    {
        return collect($this->options)
            ->map(static function (array $option): array {
                return [
                    'label_eu' => trim((string) $option['labelEu']),
                    'label_es' => trim((string) $option['labelEs']),
                ];
            })
            ->filter(fn(array $option): bool => $option['label_eu'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{label_eu: string, label_es: string}>  $normalizedOptions
     */
    private function persistVoting(array $normalizedOptions): void
    {
        $votingPayload = [
            'name_eu' => $this->nameEu,
            'name_es' => $this->nameEs !== '' ? $this->nameEs : null,
            'question_eu' => $this->questionEu,
            'question_es' => $this->questionEs !== '' ? $this->questionEs : null,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'is_published' => $this->isPublished,
            'is_anonymous' => $this->isAnonymous,
        ];

        if ($this->editingVotingId !== null) {
            $voting = Voting::query()->findOrFail($this->editingVotingId);
            $voting->update($votingPayload);

            $voting->options()->delete();
        } else {
            $voting = Voting::create($votingPayload);
        }

        VotingOption::insert($this->votingOptionRows($voting, $normalizedOptions));

        if ($this->editingVotingId !== null) {
            $this->syncVotingLocations($voting);
        } else {
            $locationRows = $this->votingLocationRows($voting);

            if ($locationRows !== []) {
                VotingLocation::insert($locationRows);
            }
        }
    }

    private function syncVotingLocations(Voting $voting): void
    {
        $locationIds = collect(array_unique($this->selectedLocations))
            ->map(static fn(string $locationId): int => (int) $locationId)
            ->values()
            ->all();

        if ($locationIds === []) {
            VotingLocation::query()
                ->where('voting_id', $voting->id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            return;
        }

        VotingLocation::query()
            ->where('voting_id', $voting->id)
            ->whereNull('deleted_at')
            ->whereNotIn('location_id', $locationIds)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        $upsertRows = collect($locationIds)
            ->map(static function (int $locationId) use ($voting): array {
                return [
                    'voting_id' => $voting->id,
                    'location_id' => $locationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ];
            })
            ->all();

        VotingLocation::upsert(
            $upsertRows,
            ['voting_id', 'location_id'],
            ['updated_at', 'deleted_at']
        );
    }

    /**
     * @param  array<int, array{label_eu: string, label_es: string}>  $normalizedOptions
     * @return array<int, array{voting_id: int, label_eu: string, label_es: ?string, position: int, created_at: \Carbon\CarbonInterface, updated_at: \Carbon\CarbonInterface}>
     */
    private function votingOptionRows(Voting $voting, array $normalizedOptions): array
    {
        return collect($normalizedOptions)
            ->values()
            ->map(static function (array $option, int $index) use ($voting): array {
                return [
                    'voting_id' => $voting->id,
                    'label_eu' => $option['label_eu'],
                    'label_es' => $option['label_es'] !== '' ? $option['label_es'] : null,
                    'position' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{voting_id: int, location_id: int, created_at: \Carbon\CarbonInterface, updated_at: \Carbon\CarbonInterface}>
     */
    private function votingLocationRows(Voting $voting): array
    {
        return collect(array_unique($this->selectedLocations))
            ->map(static function (string $locationId) use ($voting): array {
                return [
                    'voting_id' => $voting->id,
                    'location_id' => (int) $locationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();
    }

    public function openCensus(int $votingId): void
    {
        $voting = Voting::query()->with('locations.location')->findOrFail($votingId);
        $owners = $this->eligibilityService->eligibleOwnersAtVotingDate($voting);

        $ballots = VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->with('selections.option')
            ->get();

        $votedOwnerIds = $ballots->pluck('owner_id')->flip()->all();

        $votesByOwner = $ballots->mapWithKeys(function (VotingBallot $ballot): array {
            $label = $this->formatBallotOptionName($ballot);

            return [$ballot->owner_id => $label];
        })->all();

        $this->ownersModalIsAnonymous = (bool) $voting->is_anonymous;

        $this->ownersModalRows = $owners
            ->map(fn(Owner $owner): array => [
                'name' => $owner->coprop1_name,
                'has_voted' => isset($votedOwnerIds[$owner->id]),
                'vote' => $votesByOwner[$owner->id] ?? '',
                'properties' => $owner->activeAssignments
                    ->map(fn(PropertyAssignment $a): string => trim(($a->property?->location?->code ?? '') . ' ' . ($a->property?->name ?? '')))
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
        $voting = Voting::query()->with('locations.location')->findOrFail($votingId);
        $owners = $this->eligibilityService->eligibleOwnersAtVotingDate($voting)
            ->keyBy('id');

        $ballots = VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->with(['owner', 'castByUser', 'selections.option'])
            ->orderBy('voted_at')
            ->get();

        $this->ownersModalIsAnonymous = (bool) $voting->is_anonymous;

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
        $this->ownersModalTitle = '';
        $this->ownersModalRows = [];
    }

    private function formatBallotVote(VotingBallot $ballot): string
    {
        return $ballot->selections
            ->sortBy(static fn($selection) => $selection->option?->position ?? PHP_INT_MAX)
            ->map(static function ($selection): string {
                $label = $selection->option?->label ?? '';

                if ($label === '') {
                    return '';
                }

                $position = $selection->option?->position;

                return $position === null ? $label : $position . '. ' . $label;
            })
            ->filter()
            ->join(', ');
    }

    private function formatBallotOptionName(VotingBallot $ballot): string
    {
        return $ballot->selections
            ->sortBy(static fn($selection) => $selection->option?->position ?? PHP_INT_MAX)
            ->map(static function ($selection): string {
                $position = $selection->option?->position;

                if ($position === null) {
                    return match (app()->getLocale()) {
                        'eu' => 'Aukera',
                        'es' => 'Opcion',
                        default => 'Option',
                    };
                }

                return match (app()->getLocale()) {
                    'eu' => $position . '. aukera',
                    'es' => 'Opcion ' . $position,
                    default => 'Option ' . $position,
                };
            })
            ->filter()
            ->join(', ');
    }

    private function ownerModalRowAtVotingDate(Owner $owner, Voting $voting, ?VotingBallot $ballot): array
    {
        return [
            'name' => $owner->coprop1_name,
            'percentage' => $this->eligibilityService->percentageForOwnerAtVotingDate($voting, $owner),
            'vote' => $ballot instanceof VotingBallot ? $this->formatBallotOptionName($ballot) : '',
            'delegated_by' => $ballot instanceof VotingBallot
                ? ($ballot->is_in_person ? __('votings.admin.in_person_vote') : ($ballot->castByUser?->name ?? '—'))
                : '—',
            'delegate_dni' => $ballot instanceof VotingBallot ? ($ballot->cast_delegate_dni ?? '—') : '—',
            'has_voted' => $ballot instanceof VotingBallot,
            'properties' => $owner->activeAssignments
                ->map(fn(PropertyAssignment $assignment): string => trim(($assignment->property?->location?->code ?? '') . ' ' . ($assignment->property?->name ?? '')))
                ->filter()
                ->join(', '),
        ];
    }

    public function openDelegatedVoteModal(): void
    {
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

    public function render(): View
    {
        $votings = Voting::query()
            ->with(['locations.location'])
            ->withCount('ballots')
            ->orderByDesc('starts_at')
            ->paginate(10);

        $censusCounts = $this->censusCalculator->calculate($votings);

        return view('livewire.admin.votings.index', [
            'votings' => $votings,
            'censusCounts' => $censusCounts,
            'locations' => Location::query()
                ->whereIn('type', ['portal', 'local', 'garage'])
                ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 ELSE 3 END")
                ->orderBy('code')
                ->get()
                ->map(static fn(Location $l): array => [
                    'id' => (string) $l->id,
                    'label' => __('admin.locations.types.' . $l->type) . ' ' . $l->code,
                ])
                ->all(),
        ]);
    }

    private function openVoterModal(string $type): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

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
        } else {
            $this->inPersonRows = $rows;
            $this->inPersonSearch = '';
            $this->applyVoterFilter('in_person');
            $this->showInPersonModal = true;
        }
    }

    private function closeVoterModal(string $type): void
    {
        if ($type === 'delegated') {
            $this->showDelegatedModal = false;
            $this->delegatedSearch = '';
            $this->delegatedRows = [];
            $this->filteredDelegatedRows = [];
        } else {
            $this->showInPersonModal = false;
            $this->inPersonSearch = '';
            $this->inPersonRows = [];
            $this->filteredInPersonRows = [];
        }
    }

    private function startVoterSession(int $ownerId, string $type): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $allowedOwnerIds = collect($this->eligibilityService->ownersWithPendingDelegations())
            ->map(static fn(array $row): int => $row['owner']->id)
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
            static fn(array $row): bool => str_contains($row['search_index'], $search)
        ));

        if ($type === 'delegated') {
            $this->filteredDelegatedRows = $filtered;
        } else {
            $this->filteredInPersonRows = $filtered;
        }
    }

    /**
     * @return array{labelEu: string, labelEs: string}
     */
    private function emptyOptionRow(): array
    {
        return [
            'labelEu' => '',
            'labelEs' => '',
        ];
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingVotingId = null;
        $this->editingVotingBallotCount = 0;
        $this->nameEu = '';
        $this->nameEs = '';
        $this->questionEu = '';
        $this->questionEs = '';
        $this->startsAt = '';
        $this->endsAt = '';
        $this->isPublished = false;
        $this->isAnonymous = false;
        $this->selectedLocations = [];
        $this->options = [$this->emptyOptionRow()];
    }

    private function canManageAdminVotings(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasAnyRole([Role::SUPER_ADMIN]) ?? false;
    }
}
