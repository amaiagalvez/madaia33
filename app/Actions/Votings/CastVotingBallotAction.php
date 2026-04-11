<?php

namespace App\Actions\Votings;

use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
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
        ?string $castIpAddress = null,
        ?float $castLatitude = null,
        ?float $castLongitude = null,
        ?string $castDelegateDni = null,
        bool $isInPerson = false,
    ): VotingBallot {
        if (! $authenticatedUser->isSuperadmin() && ! $authenticatedUser->canVoteInVotings() && ! $authenticatedUser->canUseDelegatedVoting()) {
            throw ValidationException::withMessages([
                'vote' => __('votings.errors.not_allowed'),
            ]);
        }

        $castIpAddress = $castIpAddress ?? request()->ip();

        $ballot = DB::transaction(function () use ($voting, $owner, $optionId, $authenticatedUser, $castIpAddress, $castLatitude, $castLongitude, $castDelegateDni, $isInPerson): VotingBallot {
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

            $option = $lockedVoting->options->firstWhere('id', $optionId);

            if (! $option instanceof VotingOption) {
                throw ValidationException::withMessages([
                    'vote' => __('votings.errors.invalid_option'),
                ]);
            }

            $alreadyVoted = VotingBallot::query()
                ->where('voting_id', $lockedVoting->id)
                ->where('owner_id', $owner->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyVoted) {
                throw ValidationException::withMessages([
                    'vote' => __('votings.errors.already_voted'),
                ]);
            }

            $ballot = VotingBallot::create([
                'voting_id' => $lockedVoting->id,
                'owner_id' => $owner->id,
                'cast_by_user_id' => $authenticatedUser->id === $owner->user_id ? null : $authenticatedUser->id,
                'cast_ip_address' => $castIpAddress,
                'cast_latitude' => $castLatitude,
                'cast_longitude' => $castLongitude,
                'cast_delegate_dni' => $isInPerson ? null : $castDelegateDni,
                'is_in_person' => $isInPerson,
                'voted_at' => now(),
            ]);

            if (! $lockedVoting->is_anonymous) {
                VotingSelection::create([
                    'voting_id' => $lockedVoting->id,
                    'voting_ballot_id' => $ballot->id,
                    'owner_id' => $owner->id,
                    'voting_option_id' => $option->id,
                ]);
            }

            $total = VotingOptionTotal::query()
                ->where('voting_id', $lockedVoting->id)
                ->where('voting_option_id', $option->id)
                ->lockForUpdate()
                ->first();

            if ($total === null) {
                VotingOptionTotal::create([
                    'voting_id' => $lockedVoting->id,
                    'voting_option_id' => $option->id,
                    'votes_count' => 1,
                ]);
            } else {
                $total->increment('votes_count');
            }

            return $ballot;
        });

        $this->sendConfirmationMail($owner, $voting);

        return $ballot;
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
