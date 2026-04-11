<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Livewire\Component;
use App\Models\Location;
use App\SupportedLocales;
use Carbon\CarbonInterface;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use Livewire\WithPagination;
use App\Models\VotingLocation;
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

    public string $ownersModalTitle = '';

    /**
     * @var array<int, array{name: string, percentage: float, delegated_by: string, delegate_dni: string}>
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

        DB::transaction(fn () => $this->persistVoting($normalizedOptions));

        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', __('general.messages.saved'));
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
            ->filter(fn (array $option): bool => $option['label_eu'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{label_eu: string, label_es: string}>  $normalizedOptions
     */
    private function persistVoting(array $normalizedOptions): void
    {
        $voting = Voting::create([
            'name_eu' => $this->nameEu,
            'name_es' => $this->nameEs !== '' ? $this->nameEs : null,
            'question_eu' => $this->questionEu,
            'question_es' => $this->questionEs !== '' ? $this->questionEs : null,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'is_published' => $this->isPublished,
            'is_anonymous' => $this->isAnonymous,
        ]);

        VotingOption::insert($this->votingOptionRows($voting, $normalizedOptions));

        $locationRows = $this->votingLocationRows($voting);

        if ($locationRows !== []) {
            VotingLocation::insert($locationRows);
        }
    }

    /**
     * @param  array<int, array{label_eu: string, label_es: string}>  $normalizedOptions
     * @return array<int, array{voting_id: int, label_eu: string, label_es: ?string, position: int, created_at: CarbonInterface, updated_at: CarbonInterface}>
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
     * @return array<int, array{voting_id: int, location_id: int, created_at: CarbonInterface, updated_at: CarbonInterface}>
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
        $owners = $this->eligibilityService->eligibleOwners($voting);

        $this->ownersModalRows = $owners
            ->map(fn (Owner $owner): array => [
                'name' => $owner->coprop1_name,
                'percentage' => $this->eligibilityService->percentageForOwner($voting, $owner),
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

        $ballots = VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->with(['owner', 'castByUser'])
            ->orderBy('voted_at')
            ->get();

        $this->ownersModalRows = $ballots
            ->map(fn (VotingBallot $ballot): array => [
                'name' => $ballot->owner->coprop1_name,
                'percentage' => $ballot->owner instanceof Owner ? $this->eligibilityService->percentageForOwner($voting, $ballot->owner) : 0.0,
                'delegated_by' => $ballot->is_in_person ? __('votings.admin.in_person_vote') : $ballot->castByUser->name,
                'delegate_dni' => $ballot->cast_delegate_dni ?? '—',
            ])
            ->values()
            ->all();

        $this->ownersModalTitle = __('votings.admin.voters_modal_title', ['name' => $voting->name]);
        $this->showOwnersModal = true;
    }

    public function closeOwnersModal(): void
    {
        $this->showOwnersModal = false;
        $this->ownersModalTitle = '';
        $this->ownersModalRows = [];
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
            ->current()
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
                ->get(),
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
