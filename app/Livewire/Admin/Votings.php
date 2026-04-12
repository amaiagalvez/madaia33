<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Voting;
use Livewire\Component;
use App\Models\Location;
use Carbon\CarbonInterface;
use App\Models\VotingOption;
use Livewire\WithPagination;
use App\Models\VotingLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Support\VotingCensusCalculator;
use App\Support\VotingEligibilityService;
use Illuminate\Database\Eloquent\Builder;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Livewire\Admin\Concerns\HandlesVotingOwnerModals;

/** @SuppressWarnings("PHPMD.ExcessiveClassLength") */
class Votings extends Component
{
    use BuildsLocaleFieldConfigs;
    use HandlesVotingOwnerModals;
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

    /**
     * @var array<int, string>
     */
    public array $selectedVotingIds = [];

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
        abort_unless($this->canManageAdminVotings(), 403);

        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function editVoting(int $votingId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $voting = Voting::query()
            ->with(['options', 'locations'])
            ->findOrFail($votingId);

        abort_unless($this->canAccessVoting($voting), 403);

        $this->editingVotingId = $voting->id;
        $this->editingVotingBallotCount = $voting->ballots()->count();
        $this->nameEu = $voting->name_eu;
        $this->nameEs = (string) ($voting->name_es ?? '');
        $this->questionEu = $voting->question_eu;
        $this->questionEs = (string) ($voting->question_es ?? '');
        $this->startsAt = Carbon::parse($voting->starts_at)->format('Y-m-d');
        $this->endsAt = Carbon::parse($voting->ends_at)->format('Y-m-d');
        $this->isPublished = (bool) $voting->is_published;
        $this->isAnonymous = (bool) $voting->is_anonymous;
        $this->selectedLocations = $voting->locations
            ->map(static fn (VotingLocation $location): string => (string) $location->location_id)
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

        DB::transaction(fn () => $this->persistVoting($normalizedOptions));

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

        $voting = Voting::query()->findOrFail($votingId);

        abort_unless($this->canAccessVoting($voting), 403);

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

        abort_unless($this->canAccessVoting($voting), 403);

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

    public function downloadDelegatedPdf(): void
    {
        $this->redirectToPdf('admin.votings.pdf.delegated');
    }

    public function downloadInPersonPdf(): void
    {
        $this->redirectToPdf('admin.votings.pdf.in_person');
    }

    public function downloadResultsPdf(): void
    {
        $this->redirectToPdf('admin.votings.pdf.results');
    }

    /**
     * @param  array<int, string>  $pageIds
     */
    public function selectAllOnPage(array $pageIds): void
    {
        $this->selectedVotingIds = collect($this->selectedVotingIds)
            ->merge($pageIds)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $pageIds
     */
    public function deselectAllOnPage(array $pageIds): void
    {
        $this->selectedVotingIds = collect($this->selectedVotingIds)
            ->reject(static fn (string $id): bool => in_array($id, $pageIds, true))
            ->values()
            ->all();
    }

    public function updatingPage(): void
    {
        $this->selectedVotingIds = [];
    }

    private function redirectToPdf(string $routeName): void
    {
        $selectedIds = $this->normalizedSelectedVotingIds();

        if ($selectedIds === []) {
            session()->flash('error', __('votings.admin.select_for_pdf_required'));

            return;
        }

        $this->redirect(URL::route($routeName, ['voting_ids' => $selectedIds]), navigate: false);
    }

    /**
     * @return array<int, int>
     */
    private function normalizedSelectedVotingIds(): array
    {
        return collect($this->selectedVotingIds)
            ->map(static fn (string|int $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function validateVotingForm(): void
    {
        $user = $this->currentUser();

        abort_unless($user !== null, 403);

        $selectedLocationsRule = ['array'];

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            $this->selectedLocations = [];
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $allowedLocationIds = $this->managedLocationIds($user)
                ->map(static fn (int $locationId): string => (string) $locationId)
                ->all();

            $this->selectedLocations = collect($this->selectedLocations)
                ->filter(static fn (string $locationId): bool => in_array($locationId, $allowedLocationIds, true))
                ->values()
                ->all();

            $selectedLocationsRule = ['required', 'array', 'min:1'];
        }

        $this->validate([
            'nameEu' => ['required', 'string', 'max:255'],
            'nameEs' => ['nullable', 'string', 'max:255'],
            'questionEu' => ['required', 'string'],
            'questionEs' => ['nullable', 'string'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['required', 'date', 'after_or_equal:startsAt'],
            'isPublished' => ['boolean'],
            'isAnonymous' => ['boolean'],
            'selectedLocations' => $selectedLocationsRule,
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
            abort_unless($this->canAccessVoting($voting), 403);

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
            ->map(static fn (string $locationId): int => (int) $locationId)
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

    public function render(): View
    {
        $votings = $this->votingsQueryForCurrentUser()
            ->with(['locations.location'])
            ->withCount('ballots')
            ->orderByDesc('starts_at')
            ->paginate(10);

        $censusCounts = $this->censusCalculator->calculate($votings);

        $votingPageIds = $votings->getCollection()
            ->pluck('id')
            ->map(static fn (int $id): string => (string) $id)
            ->all();

        return view('livewire.admin.votings.index', [
            'votings' => $votings,
            'votingPageIds' => $votingPageIds,
            'censusCounts' => $censusCounts,
            'locations' => $this->availableLocationQueryForCurrentUser()
                ->whereIn('type', ['portal', 'local', 'garage'])
                ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 ELSE 3 END")
                ->orderBy('code')
                ->get()
                ->map(static fn (Location $l): array => [
                    'id' => (string) $l->id,
                    'label' => __('admin.locations.types.' . $l->type) . ' ' . $l->code,
                ])
                ->all(),
        ]);
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
        $user = $this->currentUser();

        return $user?->hasAnyRole([Role::SUPER_ADMIN, Role::GENERAL_ADMIN, Role::COMMUNITY_ADMIN]) ?? false;
    }

    private function canAccessVoting(Voting $voting): bool
    {
        $user = $this->currentUser();

        if ($user === null) {
            return false;
        }

        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            return ! $voting->locations()->exists();
        }

        if (! $user->hasRole(Role::COMMUNITY_ADMIN)) {
            return false;
        }

        $managedLocationIds = $this->managedLocationIds($user)->all();

        if ($managedLocationIds === []) {
            return false;
        }

        return $voting->locations()->whereIn('location_id', $managedLocationIds)->exists();
    }

    private function canSeeOwnerNamesInVotingModals(): bool
    {
        return $this->currentUser()?->hasRole(Role::SUPER_ADMIN) ?? false;
    }

    private function canManageInPersonAndDelegatedSessions(): bool
    {
        return $this->currentUser()?->hasRole(Role::SUPER_ADMIN) ?? false;
    }

    /**
     * @return Builder<Voting>
     */
    private function votingsQueryForCurrentUser()
    {
        $user = $this->currentUser();

        abort_unless($user !== null, 403);

        $query = Voting::query();

        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return $query;
        }

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            return $query->whereDoesntHave('locations');
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $managedLocationIds = $this->managedLocationIds($user)->all();

            if ($managedLocationIds === []) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('locations', function ($locationsQuery) use ($managedLocationIds): void {
                $locationsQuery->whereIn('location_id', $managedLocationIds);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * @return Builder<Location>
     */
    private function availableLocationQueryForCurrentUser()
    {
        $user = $this->currentUser();

        abort_unless($user !== null, 403);

        $query = Location::query();

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            return $query->whereIn('id', $this->managedLocationIds($user));
        }

        return $query;
    }

    /**
     * @return Collection<int, int>
     */
    private function managedLocationIds(User $user)
    {
        return $user->managedLocations()
            ->pluck('locations.id')
            ->map(static fn (int $locationId): int => $locationId)
            ->values();
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
