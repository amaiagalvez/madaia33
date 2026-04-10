<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Livewire\Component;
use App\Models\VotingBallot;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Actions\CastVotingBallotAction;
use App\Support\VotingEligibilityService;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\PublicVotingController;

class PublicVotings extends Component
{
  private VotingEligibilityService $eligibilityService;

  private CastVotingBallotAction $castVotingBallotAction;

  public ?Owner $activeOwner = null;

  public bool $isDelegated = false;

  public bool $canCastVotes = true;

  /**
   * @var array<int, int>
   */
  public array $selectedOptions = [];

  public function boot(
    VotingEligibilityService $eligibilityService,
    CastVotingBallotAction $castVotingBallotAction,
  ): void {
    $this->eligibilityService = $eligibilityService;
    $this->castVotingBallotAction = $castVotingBallotAction;
  }

  public function mount(): void
  {
    $user = $this->currentUser();

    abort_unless($user !== null, 403);

    if ($user->isSuperadmin()) {
      $this->canCastVotes = false;

      return;
    }

    abort_unless($user->canVoteInVotings() || $user->canUseDelegatedVoting(), 403);

    $this->activeOwner = $this->resolveOwner();
  }

  public function vote(int $votingId): void
  {
    if (! $this->canCastVotes) {
      $this->addError("selectedOptions.$votingId", __('votings.errors.not_allowed'));

      return;
    }

    $voting = Voting::query()
      ->with(['options', 'locations.location'])
      ->publishedOpen()
      ->findOrFail($votingId);

    $selectedOptionId = $this->selectedOptions[$votingId] ?? null;

    if ($selectedOptionId === null) {
      $this->addError("selectedOptions.$votingId", __('votings.errors.option_required'));

      return;
    }

    if (! $voting->options->pluck('id')->contains($selectedOptionId)) {
      $this->addError("selectedOptions.$votingId", __('votings.errors.invalid_option'));

      return;
    }

    try {
      $user = $this->currentUser();

      abort_unless($user !== null, 403);

      $this->castVotingBallotAction->execute(
        $voting,
        $this->activeOwner,
        (int) $selectedOptionId,
        $user,
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

  public function clearDelegatedMode(): void
  {
    if (! $this->canCastVotes) {
      return;
    }

    session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
    $this->isDelegated = false;
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
      ->openEligibleVotingsForOwner($this->activeOwner)
      ->load('ballots');

    abort_if($votings->isEmpty(), 404);

    $votedVotingIds = VotingBallot::query()
      ->where('owner_id', $this->activeOwner->id)
      ->whereIn('voting_id', $votings->pluck('id'))
      ->pluck('voting_id')
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

    if ($delegatedOwnerId > 0 && $user?->owner !== null) {
      session()->forget(PublicVotingController::DELEGATED_OWNER_SESSION_KEY);
      $delegatedOwnerId = 0;
    }

    if ($delegatedOwnerId > 0) {
      $this->isDelegated = true;

      return Owner::query()
        ->with(['user', 'activeAssignments.property.location'])
        ->findOrFail($delegatedOwnerId);
    }

    $owner = $user?->owner;

    abort_unless($owner instanceof Owner, 404);

    $this->isDelegated = false;

    return $owner->loadMissing(['user', 'activeAssignments.property.location']);
  }

  private function currentUser(): ?User
  {
    /** @var User|null $user */
    $user = Auth::user();

    return $user;
  }
}
