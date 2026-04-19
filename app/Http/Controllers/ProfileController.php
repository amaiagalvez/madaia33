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
use App\Models\ContactMessage;
use Illuminate\Support\Carbon;
use App\Models\UserLoginSession;
use App\Models\CampaignRecipient;
use Illuminate\Support\Collection;
use App\Support\OwnerAuditFieldLabel;
use Illuminate\Http\RedirectResponse;
use App\Validations\OwnerFormValidation;
use App\Support\VotingEligibilityService;

class ProfileController extends Controller
{
    private const DIRECT_MESSAGES_CAMPAIGN_ID = 1;

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

        $ownerBallotVotingIds = $this->ownerBallotVotingIds($owner);

        return view('public.profile', [
            'activeTab' => $this->resolveProfileTab($request, $requiresTermsAcceptance, $pendingAssignments),
            'activeAssignments' => $activeAssignments,
            'loginSessions' => $this->loginSessions($user?->id),
            'missedClosedVotings' => $this->missedClosedVotings($owner, $ownerBallotVotingIds),
            'owner' => $owner,
            'ownerAuditLogs' => $this->ownerAuditLogs($owner),
            'pendingActiveVotings' => $this->pendingActiveVotings($owner, $ownerBallotVotingIds),
            'pendingAssignments' => $pendingAssignments,
            'receivedMessages' => collect($this->receivedMessages($owner)),
            'requiresTermsAcceptance' => $requiresTermsAcceptance,
            'termsHtml' => $this->profileTermsHtml(),
            'userBallots' => $this->userBallots($user?->id, $owner?->id),
            'userMessages' => collect($this->userMessages($user?->id)),
        ]);
    }

    /**
     * @param  Collection<int, mixed>  $pendingAssignments
     */
    private function resolveProfileTab(Request $request, bool $requiresTermsAcceptance, Collection $pendingAssignments): string
    {
        $tabs = ['overview', 'votings', 'sessions', 'received', 'messages', 'owner'];
        $requestedTab = (string) $request->query('tab', '');

        if (in_array($requestedTab, $tabs, true)) {
            return $requestedTab;
        }

        return $requiresTermsAcceptance || $pendingAssignments->isNotEmpty() ? 'owner' : 'overview';
    }

    private function profileTermsHtml(): string
    {
        return Setting::localizedString(
            'owners_terms_text',
            __('profile.terms.default_text'),
        ) ?? __('profile.terms.default_text');
    }

    public function acceptTerms(Request $request): RedirectResponse
    {
        $user = $request->user();
        $returnTo = $this->resolveReturnTo($request);
        $termsScope = $this->resolveTermsScope($request);

        if ($user === null) {
            return redirect()->route(SupportedLocales::routeName('profile'));
        }

        if ($this->shouldAcceptOwnerTerms($user->owner, $termsScope)) {
            $owner = $user->owner;

            $owner->forceFill([
                'accepted_terms_at' => now(),
            ])->save();
        }

        if ($this->shouldAcceptDelegatedVoteTerms($user, $termsScope)) {
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

    private function resolveReturnTo(Request $request): string
    {
        return (string) $request->input('return_to', '');
    }

    private function resolveTermsScope(Request $request): string
    {
        $termsScope = (string) $request->input('terms_scope', 'owner');

        if (! in_array($termsScope, ['owner', 'vote_delegate', 'auto'], true)) {
            return 'owner';
        }

        return $termsScope;
    }

    private function shouldAcceptOwnerTerms(?Owner $owner, string $termsScope): bool
    {
        return in_array($termsScope, ['owner', 'auto'], true)
            && $owner !== null
            && $owner->accepted_terms_at === null;
    }

    private function shouldAcceptDelegatedVoteTerms(?object $user, string $termsScope): bool
    {
        if ($user === null || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return in_array($termsScope, ['vote_delegate', 'auto'], true)
            && $user->hasRole(Role::DELEGATED_VOTE)
            && data_get($user, 'delegated_vote_terms_accepted_at') === null;
    }

    public function updateOwner(Request $request): RedirectResponse
    {
        $user = $request->user();
        $owner = $user?->owner;

        abort_if($owner === null, 403);

        $request->merge($this->sanitizeOwnerIdentityPayload($request->all()));

        $validated = $request->validate(OwnerFormValidation::profileUpdateRules($user->id));

        $owner->update([
            ...$this->ownerPrimaryFieldsFromValidated($validated),
            ...$this->ownerSecondaryFieldsFromValidated($validated),
        ]);

        return redirect()
            ->route(SupportedLocales::routeName('profile'), ['tab' => 'owner'])
            ->with('status', __('profile.owner.profile_updated'));
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function ownerPrimaryFieldsFromValidated(array $validated): array
    {
        return [
            'coprop1_name' => $validated['coprop1_name'],
            'coprop1_surname' => $validated['coprop1_surname'] ?: null,
            'coprop1_dni' => $validated['coprop1_dni'] ?: null,
            'coprop1_email' => $validated['coprop1_email'],
            'coprop1_phone' => $validated['coprop1_phone'] ?: null,
            'coprop1_has_whatsapp' => (bool) ($validated['coprop1_has_whatsapp'] ?? false),
            'language' => $validated['language'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function ownerSecondaryFieldsFromValidated(array $validated): array
    {
        return [
            'coprop2_name' => $validated['coprop2_name'] ?: null,
            'coprop2_surname' => $validated['coprop2_surname'] ?: null,
            'coprop2_dni' => $validated['coprop2_dni'] ?: null,
            'coprop2_phone' => $validated['coprop2_phone'] ?: null,
            'coprop2_has_whatsapp' => (bool) ($validated['coprop2_has_whatsapp'] ?? false),
            'coprop2_email' => $validated['coprop2_email'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizeOwnerIdentityPayload(array $payload): array
    {
        foreach (['coprop1_dni', 'coprop2_dni'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = strtoupper((string) preg_replace('/[^0-9A-Za-z]/', '', trim((string) $payload[$field])));
            }
        }

        foreach (['coprop1_phone', 'coprop2_phone'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (string) preg_replace('/[^0-9]/', '', trim((string) $payload[$field]));
            }
        }

        return $payload;
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
    private function userBallots(?int $userId, ?int $ownerId): Collection
    {
        if ($userId === null && $ownerId === null) {
            return collect();
        }

        return VotingBallot::query()
            ->where(static function ($query) use ($userId, $ownerId): void {
                if ($ownerId !== null) {
                    $query->where('owner_id', $ownerId);
                }

                if ($userId !== null) {
                    $method = $ownerId !== null ? 'orWhere' : 'where';
                    $query->{$method}('cast_by_user_id', $userId);
                }
            })
            ->with([
                'voting' => static function ($query): void {
                    $query
                        ->withTrashed()
                        ->select(['id', 'name_eu', 'name_es', 'starts_at', 'ends_at']);
                },
            ])
            ->orderByDesc('voted_at')
            ->get(['id', 'voting_id', 'voted_at'])
            ->map(static fn (VotingBallot $ballot): array => [
                'id' => $ballot->id,
                'voting_name' => (string) data_get($ballot->voting, 'name', '—'),
                'voted_at' => Carbon::parse($ballot->voted_at),
            ]);
    }

    /**
     * @return array<int, array{id: int, subject: string, message: string, is_read: bool, created_at: Carbon, read_at: ?Carbon}>
     */
    private function userMessages(?int $userId): array
    {
        if ($userId === null) {
            return [];
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
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, subject: string, message: string, status_label: string, sent_at: Carbon}>
     *
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    private function receivedMessages(?Owner $owner): array
    {
        if ($owner === null) {
            return [];
        }

        return CampaignRecipient::query()
            ->where('owner_id', $owner->id)
            ->with([
                'campaign:id,subject_eu,subject_es,body_eu,body_es,sent_at,created_at',
                'trackingEvents:campaign_recipient_id,event_type',
            ])
            ->orderByDesc('id')
            ->get(['id', 'campaign_id', 'owner_id', 'status', 'message_subject', 'message_body', 'sent_at', 'created_at'])
            ->filter(static fn (CampaignRecipient $recipient): bool => $recipient->campaign !== null)
            ->map(function (CampaignRecipient $recipient): array {
                /** @var object $campaign */
                $campaign = $recipient->campaign;
                $locale = SupportedLocales::normalize(app()->getLocale());
                $isOpened = $recipient->trackingEvents->contains('event_type', 'open');
                $campaignSubject = $locale === SupportedLocales::SPANISH
                    ? (string) data_get($campaign, 'subject_es', '')
                    : (string) data_get($campaign, 'subject_eu', '');
                $campaignBody = $locale === SupportedLocales::SPANISH
                    ? (string) data_get($campaign, 'body_es', '')
                    : (string) data_get($campaign, 'body_eu', '');
                $hasDirectMessageContent = filled($recipient->message_subject) || filled($recipient->message_body);
                $isDirectMessage = $recipient->campaign_id === self::DIRECT_MESSAGES_CAMPAIGN_ID && $hasDirectMessageContent;
                $subject = $isDirectMessage ? (string) ($recipient->message_subject ?? '') : $campaignSubject;
                $body = $isDirectMessage ? (string) ($recipient->message_body ?? '') : $campaignBody;

                return [
                    'id' => $recipient->id,
                    'subject' => (string) ($subject ?: '—'),
                    'message' => trim(strip_tags((string) $body)) !== '' ? trim(strip_tags((string) $body)) : '—',
                    'status_label' => $isOpened
                        ? __('profile.received.opened')
                        : __('campaigns.admin.statuses.' . $recipient->status),
                    'sent_at' => $recipient->sent_at !== null
                        ? Carbon::parse((string) $recipient->sent_at)
                        : (data_get($campaign, 'sent_at') !== null
                            ? Carbon::parse((string) data_get($campaign, 'sent_at'))
                            : Carbon::parse($recipient->created_at)),
                ];
            })
            ->values()
            ->all();
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
            ->map(static function ($log): array {
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
