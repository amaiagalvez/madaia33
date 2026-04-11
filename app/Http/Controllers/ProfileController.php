<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Setting;
use App\SupportedLocales;
use Illuminate\View\View;
use App\Models\VotingBallot;
use Illuminate\Http\Request;
use App\Models\UserLoginSession;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $owner = $user?->owner()
            ->with([
                'assignments' => static function ($query): void {
                    $query
                        ->with('property.location')
                        ->orderByDesc('start_date');
                },
            ])
            ->first();

        $activeAssignments = $this->activeAssignments($owner);
        $pendingAssignments = $activeAssignments->filter(
            static fn ($assignment): bool => ! (bool) $assignment->owner_validated,
        );

        $requiresTermsAcceptance = $owner !== null && $user?->accepted_terms_at === null;

        $tabs = ['overview', 'votings', 'sessions', 'owner'];
        $requestedTab = (string) $request->query('tab', '');
        $activeTab = in_array($requestedTab, $tabs, true)
            ? $requestedTab
            : ($requiresTermsAcceptance || $pendingAssignments->isNotEmpty() ? 'owner' : 'overview');

        $termsHtml = Setting::localizedString(
            'owners_terms_text',
            __('profile.terms.default_text'),
        ) ?? __('profile.terms.default_text');

        return view('public.profile', [
            'activeTab' => $activeTab,
            'activeAssignments' => $activeAssignments,
            'loginSessions' => $this->loginSessions($user?->id),
            'owner' => $owner,
            'pendingAssignments' => $pendingAssignments,
            'requiresTermsAcceptance' => $requiresTermsAcceptance,
            'termsHtml' => $termsHtml,
            'userBallots' => $this->userBallots($user?->id),
        ]);
    }

    public function acceptTerms(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || $user->owner()->doesntExist()) {
            return redirect()->route(SupportedLocales::routeName('profile'));
        }

        $user->forceFill([
            'accepted_terms_at' => now(),
        ])->save();

        return redirect()
            ->route(SupportedLocales::routeName('profile'), ['tab' => 'owner'])
            ->with('status', __('profile.terms.accepted'));
    }

    public function validateAssignments(Request $request): RedirectResponse
    {
        $user = $request->user();
        $owner = $user?->owner;

        abort_if($owner === null, 403);

        $assignmentIds = collect((array) $request->input('assignment_ids', []))
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values();

        if ($assignmentIds->isEmpty()) {
            return redirect()
                ->route(SupportedLocales::routeName('profile'), ['tab' => 'owner'])
                ->withErrors([
                    'assignment_ids' => __('profile.owner.validation_selection_required'),
                ]);
        }

        $validatedCount = $owner->assignments()
            ->whereIn('id', $assignmentIds)
            ->whereNull('end_date')
            ->where('owner_validated', false)
            ->update([
                'owner_validated' => true,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route(SupportedLocales::routeName('profile'), ['tab' => 'owner'])
            ->with('status', __('profile.owner.validation_done', ['count' => $validatedCount]));
    }

    private function activeAssignments(?Owner $owner): Collection
    {
        if ($owner === null) {
            return collect();
        }

        return $owner->assignments
            ->filter(static fn ($assignment): bool => $assignment->end_date === null)
            ->values();
    }

    private function loginSessions(?int $userId): Collection
    {
        if ($userId === null) {
            return collect();
        }

        return UserLoginSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('logged_in_at')
            ->get(['id', 'logged_in_at', 'logged_out_at', 'ip_address'])
            ->map(static function (UserLoginSession $session): array {
                $seconds = $session->logged_out_at?->diffInSeconds($session->logged_in_at);

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'logged_in_at' => $session->logged_in_at,
                    'logged_out_at' => $session->logged_out_at,
                    'duration' => $seconds === null ? null : gmdate('H:i:s', $seconds),
                ];
            });
    }

    private function userBallots(?int $userId): Collection
    {
        if ($userId === null) {
            return collect();
        }

        return VotingBallot::query()
            ->where('cast_by_user_id', $userId)
            ->with('voting:id,name,start_date,end_date')
            ->orderByDesc('voted_at')
            ->get(['id', 'voting_id', 'voted_at'])
            ->map(static fn (VotingBallot $ballot): array => [
                'id' => $ballot->id,
                'voting_name' => $ballot->voting?->name ?? '—',
                'voted_at' => $ballot->voted_at,
            ]);
    }
}
