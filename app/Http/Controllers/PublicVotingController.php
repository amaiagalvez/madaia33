<?php

namespace App\Http\Controllers;

use App\Models\Voting;
use App\Support\VotingEligibilityService;
use App\SupportedLocales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicVotingController extends Controller
{
    public const DELEGATED_OWNER_SESSION_KEY = 'delegated_voting_owner_id';

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

        return redirect()->route(SupportedLocales::routeName('votings'));
    }
}
