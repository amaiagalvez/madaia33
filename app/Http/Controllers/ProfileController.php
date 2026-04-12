<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Setting;
use App\SupportedLocales;
use Illuminate\View\View;
use App\Models\VotingBallot;
use Illuminate\Http\Request;
use App\Models\OwnerAuditLog;
use App\Models\ContactMessage;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Models\UserLoginSession;
use Illuminate\Support\Collection;
use App\Support\OwnerAuditFieldLabel;
use Illuminate\Http\RedirectResponse;
use App\Support\VotingEligibilityService;

class ProfileController extends Controller
{
    public function __construct(private VotingEligibilityService $votingEligibilityService) {}

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

        $requiresTermsAcceptance = $owner !== null && $owner->accepted_terms_at === null;

        $tabs = ['overview', 'votings', 'sessions', 'messages', 'owner'];
        $requestedTab = (string) $request->query('tab', '');
        $activeTab = in_array($requestedTab, $tabs, true)
            ? $requestedTab
            : ($requiresTermsAcceptance || $pendingAssignments->isNotEmpty() ? 'owner' : 'overview');

        $termsHtml = Setting::localizedString(
            'owners_terms_text',
            __('profile.terms.default_text'),
        ) ?? __('profile.terms.default_text');

        $ownerBallotVotingIds = $this->ownerBallotVotingIds($owner);

        return view('public.profile', [
            'activeTab' => $activeTab,
            'activeAssignments' => $activeAssignments,
            'loginSessions' => $this->loginSessions($user?->id),
            'missedClosedVotings' => $this->missedClosedVotings($owner, $ownerBallotVotingIds),
            'owner' => $owner,
            'ownerAuditLogs' => $this->ownerAuditLogs($owner),
            'pendingActiveVotings' => $this->pendingActiveVotings($owner, $ownerBallotVotingIds),
            'pendingAssignments' => $pendingAssignments,
            'requiresTermsAcceptance' => $requiresTermsAcceptance,
            'termsHtml' => $termsHtml,
            'userBallots' => $this->userBallots($user?->id),
            'userMessages' => $this->userMessages($user?->id),
        ]);
    }

    public function acceptTerms(Request $request): RedirectResponse
    {
        $user = $request->user();
        $owner = $user?->owner;
        $returnTo = (string) $request->input('return_to', '');
        $termsScope = (string) $request->input('terms_scope', 'owner');

        if (! in_array($termsScope, ['owner', 'vote_delegate', 'auto'], true)) {
            $termsScope = 'owner';
        }

        if ($user === null) {
            return redirect()->route(SupportedLocales::routeName('profile'));
        }

        if (($termsScope === 'owner' || $termsScope === 'auto') && $owner !== null && $owner->accepted_terms_at === null) {
            $owner->forceFill([
                'accepted_terms_at' => now(),
            ])->save();
        }

        if (($termsScope === 'vote_delegate' || $termsScope === 'auto')
            && $user->hasRole(Role::DELEGATED_VOTE)
            && $user->delegated_vote_terms_accepted_at === null
        ) {
            $user->forceFill([
                'delegated_vote_terms_accepted_at' => now(),
            ])->save();
        }

        if ($returnTo !== '' && str_starts_with($returnTo, '/')) {
            return redirect($returnTo)->with('status', __('profile.terms.accepted'));
        }

        return redirect()
            ->route(SupportedLocales::routeName('profile'), ['tab' => 'owner'])
            ->with('status', __('profile.terms.accepted'));
    }

    public function updateOwner(Request $request): RedirectResponse
    {
        $user = $request->user();
        $owner = $user?->owner;

        abort_if($owner === null, 403);

        $validated = $request->validate([
            'coprop1_name' => ['required', 'string', 'max:255'],
            'coprop1_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'coprop1_phone' => ['nullable', 'string', 'max:20'],
            'language' => ['required', 'in:eu,es'],
            'coprop2_name' => ['nullable', 'string', 'max:255'],
            'coprop2_dni' => ['nullable', 'string', 'max:20'],
            'coprop2_phone' => ['nullable', 'string', 'max:20'],
            'coprop2_email' => ['nullable', 'email', 'max:255'],
        ]);

        $owner->update($validated);

        return redirect()
            ->route(SupportedLocales::routeName('profile'), ['tab' => 'owner'])
            ->with('status', __('profile.owner.profile_updated'));
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

    /**
     * @return Collection<int, mixed>
     */
    private function activeAssignments(?Owner $owner): Collection
    {
        if ($owner === null) {
            return collect();
        }

        return $owner->assignments
            ->filter(static fn ($assignment): bool => $assignment->end_date === null)
            ->values();
    }

    /**
     * @return array<int, array{id: int, ip_address: ?string, logged_in_at: Carbon, logged_out_at: ?Carbon, duration: ?string}>
     */
    private function loginSessions(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }

        return UserLoginSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('logged_in_at')
            ->get(['id', 'logged_in_at', 'logged_out_at', 'ip_address'])
            ->map(static function (UserLoginSession $session): array {
                $loggedInAt = Carbon::parse($session->logged_in_at);
                $loggedOutAt = $session->logged_out_at !== null ? Carbon::parse($session->logged_out_at) : null;
                $seconds = $loggedOutAt !== null
                    ? $loggedOutAt->diffInSeconds($loggedInAt)
                    : null;

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'logged_in_at' => $loggedInAt,
                    'logged_out_at' => $loggedOutAt,
                    'duration' => $seconds === null ? null : gmdate('H:i:s', (int) $seconds),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array{id: int, voting_name: string, voted_at: Carbon}>
     */
    private function userBallots(?int $userId): Collection
    {
        if ($userId === null) {
            return collect();
        }

        return VotingBallot::query()
            ->where('cast_by_user_id', $userId)
            ->with('voting:id,name_eu,name_es,starts_at,ends_at')
            ->orderByDesc('voted_at')
            ->get(['id', 'voting_id', 'voted_at'])
            ->map(static fn (VotingBallot $ballot): array => [
                'id' => $ballot->id,
                'voting_name' => $ballot->voting->name,
                'voted_at' => Carbon::parse($ballot->voted_at),
            ]);
    }

    /**
     * @return Collection<int, array{id: int, subject: string, message: string, is_read: bool, created_at: Carbon, read_at: ?Carbon}>
     */
    private function userMessages(?int $userId): Collection
    {
        if ($userId === null) {
            return collect();
        }

        return ContactMessage::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'subject', 'message', 'is_read', 'created_at', 'read_at'])
            ->map(static fn (ContactMessage $message): array => [
                'id' => $message->id,
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => (bool) $message->is_read,
                'created_at' => Carbon::parse($message->created_at),
                'read_at' => $message->read_at !== null ? Carbon::parse($message->read_at) : null,
            ]);
    }

    /**
     * @return Collection<int, int>
     */
    private function ownerBallotVotingIds(?Owner $owner): Collection
    {
        if ($owner === null) {
            return collect();
        }

        return VotingBallot::query()
            ->where('owner_id', $owner->id)
            ->pluck('voting_id')
            ->map(static fn (mixed $votingId): int => (int) $votingId)
            ->unique()
            ->values();
    }

    /**
     * @param  Collection<int, int>  $ownerBallotVotingIds
     * @return Collection<int, array{id: int, voting_name: string, starts_at: Carbon, ends_at: Carbon}>
     */
    private function pendingActiveVotings(?Owner $owner, Collection $ownerBallotVotingIds): Collection
    {
        if ($owner === null) {
            return collect();
        }

        return $this->votingEligibilityService
            ->openEligibleVotingsForOwner($owner)
            ->reject(fn (Voting $voting): bool => $ownerBallotVotingIds->contains($voting->id))
            ->values()
            ->map(static fn (Voting $voting): array => [
                'id' => $voting->id,
                'voting_name' => $voting->name,
                'starts_at' => Carbon::parse($voting->starts_at),
                'ends_at' => Carbon::parse($voting->ends_at),
            ]);
    }

    /**
     * @param  Collection<int, int>  $ownerBallotVotingIds
     * @return Collection<int, array{id: int, voting_name: string, starts_at: Carbon, ends_at: Carbon}>
     */
    private function missedClosedVotings(?Owner $owner, Collection $ownerBallotVotingIds): Collection
    {
        if ($owner === null) {
            return collect();
        }

        return Voting::query()
            ->where('is_published', true)
            ->whereDate('ends_at', '<', today())
            ->with('locations.location')
            ->orderByDesc('ends_at')
            ->get(['id', 'name_eu', 'name_es', 'starts_at', 'ends_at'])
            ->reject(fn (Voting $voting): bool => $ownerBallotVotingIds->contains($voting->id))
            ->filter(fn (Voting $voting): bool => $this->votingEligibilityService->ownerCanVoteAtVotingDate($voting, $owner))
            ->values()
            ->map(static fn (Voting $voting): array => [
                'id' => $voting->id,
                'voting_name' => $voting->name,
                'starts_at' => Carbon::parse($voting->starts_at),
                'ends_at' => Carbon::parse($voting->ends_at),
            ]);
    }

    /**
     * @return array<int, array{field_label: string, old_value: string, new_value: string, changed_by: string, changed_at: string}>
     */
    private function ownerAuditLogs(?Owner $owner): array
    {
        if (! $owner instanceof Owner) {
            return [];
        }

        return $owner->auditLogs()
            ->with('changedBy:id,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(static function (OwnerAuditLog $log): array {
                return [
                    'field_label' => OwnerAuditFieldLabel::for($log->field),
                    'old_value' => $log->old_value !== '' ? $log->old_value : '—',
                    'new_value' => $log->new_value !== '' ? $log->new_value : '—',
                    'changed_by' => data_get($log, 'changedBy.name') ?? __('admin.owners.audit.system'),
                    'changed_at' => $log->created_at?->format('d/m/Y H:i') ?? '—',
                ];
            })
            ->values()
            ->all();
    }
}
