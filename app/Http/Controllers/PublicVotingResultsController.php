<?php

namespace App\Http\Controllers;

use App\Models\Voting;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Actions\Votings\BuildVotingResultsTableAction;

class PublicVotingResultsController extends Controller
{
    public function show(
        Request $request,
        Voting $voting,
        BuildVotingResultsTableAction $buildVotingResultsTableAction,
    ): View {
        abort_unless((bool) $voting->show_results, 404);

        $voting->load('locations.location');

        $table = $buildVotingResultsTableAction->execute($voting, canSeeOwnerNames: false);

        $votingsWithResults = Voting::query()
            ->where('show_results', true)
            ->orderByDesc('ends_at')
            ->get(['id', 'name_eu', 'name_es']);

        return view('public.voting-results', [
            'voting' => $voting,
            'options' => $table['options'],
            'charts' => $table['charts'],
            'isAnonymous' => $table['is_anonymous'],
            'votingsWithResults' => $votingsWithResults,
        ]);
    }
}
