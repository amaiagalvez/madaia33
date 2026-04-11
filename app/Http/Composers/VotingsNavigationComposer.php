<?php

namespace App\Http\Composers;

use App\Models\Voting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Support\VotingEligibilityService;

class VotingsNavigationComposer
{
    public function __construct(
        private VotingEligibilityService $eligibilityService,
    ) {}

    public function compose(View $view): void
    {
        try {
            if (! Auth::check()) {
                $view->with('showVotingsLink', false);

                return;
            }

            $hasOpenVotings = Voting::query()->publishedOpen()->exists();
            $hasPendingDelegations = $this->eligibilityService->ownersWithPendingDelegations()->isNotEmpty();

            $showVotingsLink = $hasOpenVotings || $hasPendingDelegations;
            $view->with('showVotingsLink', $showVotingsLink);
        } catch (\Throwable $exception) {
            report($exception);
            $view->with('showVotingsLink', false);
        }
    }
}
