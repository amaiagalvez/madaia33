<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Voting;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\AdminVotingAccessService;
use App\Actions\Votings\BuildVotingResultsTableAction;

class VotingResultsController extends Controller
{
    public function show(
        Request $request,
        Voting $voting,
        BuildVotingResultsTableAction $buildVotingResultsTableAction,
        AdminVotingAccessService $adminVotingAccessService,
    ): View {
        $user = $request->user();

        abort_unless($user instanceof User, 403);
        abort_unless($adminVotingAccessService->canManage($user), 403);

        $voting->load('locations.location');

        abort_unless($adminVotingAccessService->canAccess($user, $voting), 403);

        $table = $buildVotingResultsTableAction->execute(
            $voting,
            $user->hasRole(Role::SUPER_ADMIN),
        );

        return view('admin.voting-results', [
            'voting' => $voting,
            'rows' => $table['rows'],
            'options' => $table['options'],
            'charts' => $table['charts'],
            'isAnonymous' => $table['is_anonymous'],
        ]);
    }
}
