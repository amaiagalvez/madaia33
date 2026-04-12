<?php

namespace App\Actions\Votings;

use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\VotingConfirmationMail;
use Illuminate\Support\Facades\Mail;
use App\Support\VotingEligibilityService;
use Illuminate\Validation\ValidationException;

class CastVotingBallotAction
{
    public function __construct(
        private readonly VotingEligibilityService $eligibilityService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(
        Voting $voting,
        Owner $owner,
        int $optionId,
        User $authenticatedUser,
        ?CastVotingData $castData = null,
    ): VotingBallot {
        $this->validateUserPermissions($authenticatedUser);
        $castData ??= CastVotingData::fromInputs();

        $ballot = DB::transaction(function () use ($voting, $owner, $optionId, $authenticatedUser, $castData): VotingBallot {
            $lockedVoting = $this->lockAndValidateVoting($voting, $owner, $optionId);
            $option = $lockedVoting->options->firstWhere('id', $optionId);

            $this->validateOption($option);
            $this->validateNotAlreadyVoted($lockedVoting, $owner);

            $ballot = $this->createBallot($lockedVoting, $owner, $authenticatedUser, $option, $castData);

            return $ballot;
        });

        $this->sendConfirmationMail($owner, $voting);

        return $ballot;
    }

    private function validateUserPermissions(User $user): void
    {
        if (! $user->isSuperadmin() && ! $user->canVoteInVotings() && ! $user->canUseDelegatedVoting()) {
            throw ValidationException::withMessages([
                'vote' => __('votings.errors.not_allowed'),
            ]);
        }
    }

    private function lockAndValidateVoting(Voting $voting, Owner $owner, int $optionId): Voting
    {
        $lockedVoting = Voting::query()
            ->with(['options', 'locations.location'])
            ->lockForUpdate()
            ->findOrFail($voting->id);

        if (! $lockedVoting->is_published || ! $lockedVoting->isOpen()) {
            throw ValidationException::withMessages([
                'vote' => __('votings.errors.closed'),
            ]);
        }

        if (! $this->eligibilityService->ownerCanVote($lockedVoting, $owner)) {
            throw ValidationException::withMessages([
                'vote' => __('votings.errors.not_allowed'),
            ]);
        }

        return $lockedVoting;
    }

    private function validateOption(?VotingOption $option): void
    {
        if (! $option instanceof VotingOption) {
            throw ValidationException::withMessages([
                'vote' => __('votings.errors.invalid_option'),
            ]);
        }
    }

    private function validateNotAlreadyVoted(Voting $voting, Owner $owner): void
    {
        $alreadyVoted = VotingBallot::query()
            ->where('voting_id', $voting->id)
            ->where('owner_id', $owner->id)
            ->lockForUpdate()
            ->exists();

        if ($alreadyVoted) {
            throw ValidationException::withMessages([
                'vote' => __('votings.errors.already_voted'),
            ]);
        }
    }

    private function createBallot(
        Voting $voting,
        Owner $owner,
        User $authenticatedUser,
        VotingOption $option,
        CastVotingData $castData,
    ): VotingBallot {
        $ballot = VotingBallot::create([
            'voting_id' => $voting->id,
            'owner_id' => $owner->id,
            'cast_by_user_id' => $authenticatedUser->id === $owner->user_id ? null : $authenticatedUser->id,
            'cast_ip_address' => $castData->ipAddress,
            'cast_latitude' => $castData->latitude,
            'cast_longitude' => $castData->longitude,
            'cast_delegate_dni' => $castData->isInPerson ? null : $castData->delegateDni,
            'is_in_person' => $castData->isInPerson,
            'voted_at' => now(),
        ]);

        if (! $voting->is_anonymous) {
            VotingSelection::create([
                'voting_id' => $voting->id,
                'voting_ballot_id' => $ballot->id,
                'owner_id' => $owner->id,
                'voting_option_id' => $option->id,
            ]);
        }

        $this->incrementVotingTotal($voting, $option, $owner);

        return $ballot;
    }

    private function incrementVotingTotal(Voting $voting, VotingOption $option, Owner $owner): void
    {
        $owner->loadMissing('activeAssignments.property');

        $ownerPct = $owner->activeAssignments
            ->sum(fn(PropertyAssignment $assignment): float => (float) ($assignment->property->community_pct ?? 0));

        $total = VotingOptionTotal::query()
            ->where('voting_id', $voting->id)
            ->where('voting_option_id', $option->id)
            ->lockForUpdate()
            ->first();

        if ($total === null) {
            VotingOptionTotal::create([
                'voting_id' => $voting->id,
                'voting_option_id' => $option->id,
                'votes_count' => 1,
                'pct_total' => $ownerPct,
            ]);
        } else {
            $total->increment('votes_count');
            $total->increment('pct_total', $ownerPct);
        }
    }

    private function sendConfirmationMail(Owner $owner, Voting $voting): void
    {
        $owner->loadMissing('user');

        if ($owner->user?->email === null) {
            return;
        }

        try {
            Mail::to($owner->user->email)->send(new VotingConfirmationMail($owner, $voting));
        } catch (\Throwable $throwable) {
            Log::warning('Unable to send voting confirmation mail.', [
                'owner_id' => $owner->id,
                'voting_id' => $voting->id,
                'error' => $throwable->getMessage(),
            ]);
        }
    }
}
