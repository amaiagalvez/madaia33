<?php

namespace App\Http\Controllers;

use App\Models\Voting;
use App\SupportedLocales;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Support\VotingEligibilityService;

class PublicVotingController extends Controller
{
    public const DELEGATED_OWNER_SESSION_KEY = 'delegated_voting_owner_id';

    public const IN_PERSON_OWNER_SESSION_KEY = 'in_person_voting_owner_id';

    public function index(): View
    {
        $hasOpenVotings = Voting::query()->publishedOpen()->exists();
        $hasPendingDelegations = app(VotingEligibilityService::class)->ownersWithPendingDelegations()->isNotEmpty();

        abort_unless($hasOpenVotings || $hasPendingDelegations, 404);

        return view('public.votings');
    }

    public function clearDelegatedVoting(Request $request): RedirectResponse
    {
        $request->session()->forget(self::DELEGATED_OWNER_SESSION_KEY);
        $request->session()->forget(self::IN_PERSON_OWNER_SESSION_KEY);

        return redirect()->route(SupportedLocales::routeName('votings'));
    }
}
