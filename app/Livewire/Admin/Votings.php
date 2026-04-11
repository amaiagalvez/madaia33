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
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Support\VotingEligibilityService;
use App\Concerns\BuildsLocaleFieldConfigs;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\PublicVotingController;

class Votings extends Component
{
    use BuildsLocaleFieldConfigs;
    use WithPagination;

    private VotingEligibilityService $eligibilityService;

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

        $normalizedOptions = collect($this->options)
            ->map(static function (array $option): array {
                return [
                    'label_eu' => trim((string) ($option['labelEu'] ?? '')),
                    'label_es' => trim((string) ($option['labelEs'] ?? '')),
                ];
            })
            ->filter(fn (array $option): bool => $option['label_eu'] !== '')
            ->values();

        if ($normalizedOptions->isEmpty()) {
            $this->addError('options.0.labelEu', __('votings.admin.validations.option_required'));

            return;
        }

        DB::transaction(function () use ($normalizedOptions): void {
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

            $rows = $normalizedOptions
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

            VotingOption::insert($rows);

            $locationRows = collect(array_unique($this->selectedLocations))
                ->map(static function (string $locationId) use ($voting): array {
                    return [
                        'voting_id' => $voting->id,
                        'location_id' => (int) $locationId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->all();

            if ($locationRows !== []) {
                VotingLocation::insert($locationRows);
            }
        });

        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', __('general.messages.saved'));
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
                'name' => $ballot->owner?->coprop1_name ?? '—',
                'percentage' => $ballot->owner instanceof Owner ? $this->eligibilityService->percentageForOwner($voting, $ballot->owner) : 0.0,
                'delegated_by' => $ballot->is_in_person ? __('votings.admin.in_person_vote') : ($ballot->castByUser?->name ?? '—'),
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
        abort_unless($this->canManageAdminVotings(), 403);

        $this->delegatedRows = $this->eligibilityService
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

        $this->delegatedSearch = '';
        $this->applyDelegatedFilter();
        $this->showDelegatedModal = true;
    }

    public function updatedDelegatedSearch(): void
    {
        $this->applyDelegatedFilter();
    }

    public function closeDelegatedVoteModal(): void
    {
        $this->showDelegatedModal = false;
        $this->delegatedSearch = '';
        $this->delegatedRows = [];
        $this->filteredDelegatedRows = [];
    }

    public function startDelegatedVote(int $ownerId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $allowedOwnerIds = collect($this->eligibilityService->ownersWithPendingDelegations())
            ->map(static fn (array $row): int => $row['owner']->id)
            ->all();

        abort_unless(in_array($ownerId, $allowedOwnerIds, true), 404);

        session()->forget(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY);
        session()->put(PublicVotingController::DELEGATED_OWNER_SESSION_KEY, $ownerId);

        $this->redirectRoute(SupportedLocales::routeName('votings'));
    }

    public function openInPersonVoteModal(): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $this->inPersonRows = $this->eligibilityService
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

        $this->inPersonSearch = '';
        $this->applyInPersonFilter();
        $this->showInPersonModal = true;
    }

    public function updatedInPersonSearch(): void
    {
        $this->applyInPersonFilter();
    }

    public function closeInPersonVoteModal(): void
    {
        $this->showInPersonModal = false;
        $this->inPersonSearch = '';
        $this->inPersonRows = [];
        $this->filteredInPersonRows = [];
    }

    public function startInPersonVote(int $ownerId): void
    {
        abort_unless($this->canManageAdminVotings(), 403);

        $allowedOwnerIds = collect($this->eligibilityService->ownersWithPendingDelegations())
            ->map(static fn (array $row): int => $row['owner']->id)
            ->all();

        abort_unless(in_array($ownerId, $allowedOwnerIds, true), 404);

        session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
        session()->put(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY, $ownerId);

        $this->redirectRoute(SupportedLocales::routeName('votings'));
    }

    public function render(): View
    {
        $votings = Voting::query()
            ->with(['locations.location'])
            ->withCount('ballots')
            ->current()
            ->orderByDesc('starts_at')
            ->paginate(10);

        $censusCounts = $this->censusCounts($votings);

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

    /**
     * @return array<int, int>
     */
    private function censusCounts(LengthAwarePaginator $votings): array
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

        $ownerLocations = $assignmentRows
            ->groupBy('owner_id')
            ->map(static function ($rows): array {
                return [
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
                ];
            });

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

    private function applyDelegatedFilter(): void
    {
        $search = mb_strtolower(trim($this->delegatedSearch));

        if ($search === '') {
            $this->filteredDelegatedRows = $this->delegatedRows;

            return;
        }

        $this->filteredDelegatedRows = array_values(array_filter(
            $this->delegatedRows,
            static fn (array $row): bool => str_contains($row['search_index'], $search)
        ));
    }

    private function applyInPersonFilter(): void
    {
        $search = mb_strtolower(trim($this->inPersonSearch));

        if ($search === '') {
            $this->filteredInPersonRows = $this->inPersonRows;

            return;
        }

        $this->filteredInPersonRows = array_values(array_filter(
            $this->inPersonRows,
            static fn (array $row): bool => str_contains($row['search_index'], $search)
        ));
    }
}
