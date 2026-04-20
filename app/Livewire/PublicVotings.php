<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Setting;
use Livewire\Component;
use App\SupportedLocales;
use App\Models\VotingBallot;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Actions\Votings\CastVotingData;
use App\Support\VotingEligibilityService;
use Illuminate\Validation\ValidationException;
use App\Actions\Votings\CastVotingBallotAction;
use App\Http\Controllers\PublicVotingController;

/** @SuppressWarnings("PHPMD.ExcessiveClassLength") */
class PublicVotings extends Component
{
    private VotingEligibilityService $eligibilityService;

    private CastVotingBallotAction $castVotingBallotAction;

    public ?Owner $activeOwner = null;

    public bool $isDelegated = false;

    public bool $isInPersonVoting = false;

    public bool $canCastVotes = true;

    public bool $canManageDelegatedVoting = false;

    public bool $requiresTermsAcceptance = false;

    public string $termsHtml = '';

    public string $termsScope = 'owner';

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

    public string $delegateDni = '';

    public bool $showInPersonModal = false;

    public string $votingsExplanationHtml = '';

    /**
     * @var array<int, array{owner_id: int, owner_name: string, owner_secondary_name: string, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public array $inPersonRows = [];

    /**
     * @var array<int, array{owner_id: int, owner_name: string, owner_secondary_name: string, pending_votings: int, portal_codes: string, local_codes: string, garage_codes: string, search_index: string}>
     */
    public array $filteredInPersonRows = [];

    public string $inPersonSearch = '';

    /**
     * @var array<int, int>
     */
    public array $selectedOptions = [];

    public ?float $voteLatitude = null;

    public ?float $voteLongitude = null;

    public function boot(
        VotingEligibilityService $eligibilityService,
        CastVotingBallotAction $castVotingBallotAction,
    ): void {
        $this->eligibilityService = $eligibilityService;
        $this->castVotingBallotAction = $castVotingBallotAction;
    }

    public function mount(): void
    {
        $this->enableBackButtonCache();

        $user = $this->currentUser();
        $delegatedOwnerId = (int) session()->get(PublicVotingController::DELEGATED_OWNER_SESSION_KEY, 0);
        $inPersonOwnerId = (int) session()->get(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY, 0);

        abort_unless($user !== null, 403);

        $this->setTermsState($user);
        $this->setVotingsExplanationHtml();

        $this->canManageDelegatedVoting = $this->canManageDelegatedVotingForCurrentUser();

        if ($user->isSuperadmin()) {
            $this->initializeSuperadminMode($delegatedOwnerId, $inPersonOwnerId);

            return;
        }

        abort_unless(
            $user->canVoteInVotings()
                || $user->canUseDelegatedVoting()
                || $this->canAccessFrontVotingsReadOnly($user),
            403
        );

        $this->initializeOwnerVotingMode($user, $delegatedOwnerId, $inPersonOwnerId);
    }

    private function setTermsState(User $user): void
    {
        $requiresOwnerTermsAcceptance = $this->shouldRequireOwnerTermsAcceptance($user);
        $requiresDelegatedTermsAcceptance = $this->shouldRequireDelegatedTermsAcceptance($user);

        $this->requiresTermsAcceptance = $requiresOwnerTermsAcceptance || $requiresDelegatedTermsAcceptance;
        $this->termsScope = $requiresDelegatedTermsAcceptance ? 'vote_delegate' : 'owner';

        $this->termsHtml = $this->termsScope === 'vote_delegate'
            ? (Setting::localizedString(
                'vote_delegate_terms_text',
                __('votings.front.delegated_terms_default_text'),
            ) ?? __('votings.front.delegated_terms_default_text'))
            : (Setting::localizedString(
                'owners_terms_text',
                __('profile.terms.default_text'),
            ) ?? __('profile.terms.default_text'));
    }

    private function setVotingsExplanationHtml(): void
    {
        $activeLocale = SupportedLocales::normalize((string) session('locale', SupportedLocales::current()));
        $currentLocaleExplanationKey = SupportedLocales::localizedKey('votings_explanation_text', $activeLocale);
        $currentLocaleExplanationHtml = trim((string) Setting::query()
            ->where('key', $currentLocaleExplanationKey)
            ->value('value'));

        $this->votingsExplanationHtml = $currentLocaleExplanationHtml !== ''
            ? $currentLocaleExplanationHtml
            : __('votings.front.explanation_default_text');
    }

    public function openDelegatedVoteModal(): void
    {
        if (! $this->ensureTermsAccepted()) {
            return;
        }

        abort_unless($this->canManageDelegatedVotingForCurrentUser(), 403);

        $this->delegatedRows = $this->eligibilityService
            ->ownersWithPendingDelegations()
            ->map(static function (array $row): array {
                return [
                    'owner_id' => $row['owner']->id,
                    'owner_name' => $row['owner']->fullName1,
                    'owner_secondary_name' => $row['owner']->fullName2,
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
        if (! $this->ensureTermsAccepted()) {
            return;
        }

        abort_unless($this->canManageDelegatedVotingForCurrentUser(), 403);

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
        if (! $this->ensureTermsAccepted()) {
            return;
        }

        abort_unless($this->canManageDelegatedVotingForCurrentUser(), 403);

        $this->inPersonRows = $this->eligibilityService
            ->ownersWithPendingDelegations()
            ->map(static function (array $row): array {
                return [
                    'owner_id' => $row['owner']->id,
                    'owner_name' => $row['owner']->fullName1,
                    'owner_secondary_name' => $row['owner']->fullName2,
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
        if (! $this->ensureTermsAccepted()) {
            return;
        }

        abort_unless($this->canManageDelegatedVotingForCurrentUser(), 403);

        $allowedOwnerIds = collect($this->eligibilityService->ownersWithPendingDelegations())
            ->map(static fn (array $row): int => $row['owner']->id)
            ->all();

        abort_unless(in_array($ownerId, $allowedOwnerIds, true), 404);

        session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
        session()->put(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY, $ownerId);
        $this->redirectRoute(SupportedLocales::routeName('votings'));
    }

    public function vote(int $votingId): void
    {
        if (! $this->ensureTermsAccepted()) {
            $this->addError("selectedOptions.$votingId", __('profile.terms.title'));

            return;
        }

        if (! $this->canCastVotes) {
            $this->addError("selectedOptions.$votingId", __('votings.errors.not_allowed'));

            return;
        }

        $selection = $this->resolveVoteSelection($votingId);

        if ($selection === null || ! $this->hasValidDelegateDni()) {
            return;
        }

        [$voting, $selectedOptionId] = $selection;

        $this->castVote($votingId, $voting, $selectedOptionId);
    }

    private function initializeSuperadminMode(int $delegatedOwnerId, int $inPersonOwnerId): void
    {
        if ($this->hasSelectedOwnerSession($delegatedOwnerId, $inPersonOwnerId)) {
            $this->activateSelectedOwner();

            return;
        }

        $this->canCastVotes = false;
    }

    private function initializeOwnerVotingMode(User $user, int $delegatedOwnerId, int $inPersonOwnerId): void
    {
        if ($user->canUseDelegatedVoting() && $this->hasSelectedOwnerSession($delegatedOwnerId, $inPersonOwnerId)) {
            $this->activateSelectedOwner();

            return;
        }

        if ($user->canUseDelegatedVoting() && ! $this->hasSelectedOwnerSession($delegatedOwnerId, $inPersonOwnerId) && $user->owner === null) {
            $this->canCastVotes = false;

            return;
        }

        if ($this->canAccessFrontVotingsReadOnly($user) && ! $user->canVoteInVotings()) {
            $this->canCastVotes = false;

            return;
        }

        $this->activeOwner = $this->resolveOwner();
        $this->canCastVotes = $user->canVoteInVotings();
    }

    private function activateSelectedOwner(): void
    {
        $this->canCastVotes = true;
        $this->activeOwner = $this->resolveOwner();
    }

    private function hasSelectedOwnerSession(int $delegatedOwnerId, int $inPersonOwnerId): bool
    {
        return $delegatedOwnerId > 0 || $inPersonOwnerId > 0;
    }

    /**
     * @return array{0: Voting, 1: int}|null
     */
    private function resolveVoteSelection(int $votingId): ?array
    {
        $voting = Voting::query()
            ->with(['options', 'locations.location'])
            ->publishedOpen()
            ->findOrFail($votingId);

        $selectedOptionId = $this->selectedOptions[$votingId] ?? null;

        if ($selectedOptionId === null) {
            $this->addError("selectedOptions.$votingId", __('votings.errors.option_required'));

            return null;
        }

        if (! $voting->options->pluck('id')->contains($selectedOptionId)) {
            $this->addError("selectedOptions.$votingId", __('votings.errors.invalid_option'));

            return null;
        }

        return [$voting, (int) $selectedOptionId];
    }

    private function hasValidDelegateDni(): bool
    {
        if (! $this->isDelegated || trim($this->delegateDni) !== '') {
            return true;
        }

        $this->addError('delegateDni', __('votings.errors.delegate_dni_required'));

        return false;
    }

    private function castVote(int $votingId, Voting $voting, int $selectedOptionId): void
    {
        $user = $this->currentUser();

        abort_unless($user !== null, 403);
        abort_unless($this->activeOwner instanceof Owner, 404);

        try {
            $this->castVotingBallotAction->execute(
                $voting,
                $this->activeOwner,
                $selectedOptionId,
                $user,
                CastVotingData::fromInputs(
                    ipAddress: request()->ip(),
                    latitude: $this->voteLatitude,
                    longitude: $this->voteLongitude,
                    delegateDni: $this->isDelegated ? trim($this->delegateDni) : null,
                    isInPerson: $this->isInPersonVoting,
                ),
            );

            unset($this->selectedOptions[$votingId]);
            session()->flash('message', __('votings.front.vote_saved'));
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();

            if (is_string($firstError) && $firstError !== '') {
                $this->addError("selectedOptions.$votingId", $firstError);
            }
        }
    }

    public function setVoteCoordinates(?float $latitude = null, ?float $longitude = null): void
    {
        $this->voteLatitude = $this->normalizeCoordinate($latitude, -90, 90);
        $this->voteLongitude = $this->normalizeCoordinate($longitude, -180, 180);
    }

    public function clearDelegatedMode(): void
    {
        if (! $this->canCastVotes) {
            return;
        }

        session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
        session()->forget(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY);

        $this->delegateDni = '';

        if ($this->currentUser()?->isSuperadmin() || $this->currentUser()?->canUseDelegatedVoting()) {
            $this->canCastVotes = false;
            $this->isDelegated = false;
            $this->isInPersonVoting = false;
            $this->activeOwner = null;

            return;
        }

        $this->isDelegated = false;
        $this->isInPersonVoting = false;
        $this->activeOwner = $this->resolveOwner();
    }

    public function render(): View
    {
        if (! $this->canCastVotes) {
            $votings = Voting::query()
                ->with(['options', 'locations.location', 'optionTotals.option'])
                ->publishedOpen()
                ->orderBy('starts_at')
                ->get();

            abort_if($votings->isEmpty(), 404);

            return view('livewire.front.public-votings', [
                'votings' => $votings,
                'votedVotingIds' => [],
            ]);
        }

        $votings = $this->eligibilityService
            ->openEligibleVotingsForOwner($this->activeOwner);

        $votings->each(static function (Voting $voting): void {
            $voting->load('ballots');
        });

        abort_if($votings->isEmpty(), 404);

        $votedVotingIds = VotingBallot::query()
            ->where('owner_id', $this->activeOwner->id)
            ->whereIn('voting_id', $votings->pluck('id'))
            ->pluck('voting_id')
            ->map(static fn ($votingId): int => (int) $votingId)
            ->all();

        return view('livewire.front.public-votings', [
            'votings' => $votings,
            'votedVotingIds' => $votedVotingIds,
        ]);
    }

    private function resolveOwner(): Owner
    {
        $user = $this->currentUser();

        $delegatedOwnerId = (int) session()->get(PublicVotingController::DELEGATED_OWNER_SESSION_KEY, 0);
        $inPersonOwnerId = (int) session()->get(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY, 0);

        if (($delegatedOwnerId > 0 || $inPersonOwnerId > 0) && $user?->owner !== null) {
            session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
            session()->forget(PublicVotingController::IN_PERSON_OWNER_SESSION_KEY);
            $delegatedOwnerId = 0;
            $inPersonOwnerId = 0;
        }

        if ($delegatedOwnerId > 0) {
            $this->isDelegated = true;
            $this->isInPersonVoting = false;

            return Owner::query()
                ->with(['user', 'activeAssignments.property.location'])
                ->findOrFail($delegatedOwnerId);
        }

        if ($inPersonOwnerId > 0) {
            $this->isDelegated = false;
            $this->isInPersonVoting = true;

            return Owner::query()
                ->with(['user', 'activeAssignments.property.location'])
                ->findOrFail($inPersonOwnerId);
        }

        $owner = $user?->owner;

        abort_unless($owner instanceof Owner, 404);

        $this->isDelegated = false;
        $this->isInPersonVoting = false;

        return $owner->loadMissing(['user', 'activeAssignments.property.location']);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    private function normalizeCoordinate(?float $value, float $min, float $max): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value < $min || $value > $max) {
            return null;
        }

        return round($value, 7);
    }

    private function canManageDelegatedVotingForCurrentUser(): bool
    {
        $user = $this->currentUser();

        if (! $user instanceof User) {
            return false;
        }

        return $user->isSuperadmin() || $user->canUseDelegatedVoting();
    }

    private function canAccessFrontVotingsReadOnly(User $user): bool
    {
        return $user->hasAnyRole([Role::GENERAL_ADMIN, Role::COMMUNITY_ADMIN]);
    }

    private function ensureTermsAccepted(): bool
    {
        if (! $this->requiresTermsAcceptance) {
            return true;
        }

        return false;
    }

    private function shouldRequireOwnerTermsAcceptance(User $user): bool
    {
        $owner = $user->owner;

        if (! $owner instanceof Owner) {
            return false;
        }

        return $owner->accepted_terms_at === null;
    }

    private function shouldRequireDelegatedTermsAcceptance(User $user): bool
    {
        if (! $user->hasRole(Role::DELEGATED_VOTE)) {
            return false;
        }

        return $user->delegated_vote_terms_accepted_at === null;
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
