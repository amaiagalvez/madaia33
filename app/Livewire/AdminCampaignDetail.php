<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Jobs\Messaging\SendCampaignMessageJob;
use App\Actions\Campaigns\DuplicateCampaignAction;
use App\Livewire\Concerns\HandlesCampaignDetailWhatsapp;

class AdminCampaignDetail extends Component
{
    use HandlesCampaignDetailWhatsapp;

    public Campaign $campaign;

    public ?int $expandedRecipientId = null;

    public int $unopenedRecipientsCount = 0;

    public bool $showResendModal = false;

    /**
     * @var array{total: int, opens: int, clicks: int, downloads: int, failures: int}
     */
    public array $metrics = [
        'total' => 0,
        'opens' => 0,
        'clicks' => 0,
        'downloads' => 0,
        'failures' => 0,
    ];

    public function mount(Campaign $campaign): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $campaign);

        $this->campaign = $campaign;
        $this->refreshCampaign();
        $this->refreshMetrics();
    }

    public function toggleRecipientDetails(int $recipientId): void
    {
        $this->expandedRecipientId = $this->expandedRecipientId === $recipientId ? null : $recipientId;
    }

    public function duplicateCampaign(): void
    {
        $this->authorizeViewAny();
        $this->authorize('duplicate', $this->campaign);

        $user = $this->currentUser();

        abort_if($user === null, 403);

        $newCampaign = app(DuplicateCampaignAction::class)->execute($this->campaign, $user);

        session()->flash('message', __('general.messages.saved'));

        $this->redirectRoute('admin.campaigns', ['editCampaign' => $newCampaign->id], navigate: true);
    }

    public function confirmResendToUnopened(): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $this->campaign);

        abort_unless($this->campaign->status === 'completed', 403);

        if ($this->unopenedRecipientsCount === 0) {
            session()->flash('warning', __('campaigns.admin.messages.all_opened'));

            return;
        }

        $this->showResendModal = true;
    }

    public function cancelResendToUnopened(): void
    {
        $this->showResendModal = false;
    }

    public function doResendToUnopened(): void
    {
        $this->cancelResendToUnopened();
        $this->resendToUnopened();
    }

    public function markManualRecipientSent(int $recipientId): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $this->campaign);

        abort_unless($this->campaign->channel === 'manual', 403);

        $recipient = CampaignRecipient::query()
            ->where('campaign_id', $this->campaign->id)
            ->findOrFail($recipientId);

        $recipient->status = 'sent';
        $recipient->sent_at = now();
        $recipient->error_message = null;
        $recipient->save();

        session()->flash('message', __('campaigns.admin.messages.manual_marked_sent'));
    }

    public function resendToUnopened(): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $this->campaign);

        abort_unless($this->campaign->status === 'completed', 403);

        $unopenedRecipientIds = $this->campaign
            ->recipients
            ->filter(fn (CampaignRecipient $recipient): bool => ! $recipient->trackingEvents->contains('event_type', 'open'))
            ->pluck('id')
            ->values();

        if ($unopenedRecipientIds->isEmpty()) {
            session()->flash('warning', __('campaigns.admin.messages.all_opened'));

            return;
        }

        $this->campaign->status = 'sending';
        $this->campaign->save();

        CampaignRecipient::query()
            ->whereIn('id', $unopenedRecipientIds->all())
            ->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

        foreach ($unopenedRecipientIds as $recipientId) {
            dispatch(new SendCampaignMessageJob((int) $recipientId));
        }

        session()->flash('message', __('campaigns.admin.messages.resend_unopened_queued'));
    }

    public function render(): View
    {
        $this->refreshCampaign();
        $this->refreshMetrics();

        return view('livewire.admin.campaign-detail', [
            'recipientRows' => $this->recipientRows(),
            'canResendToUnopened' => $this->campaign->status === 'completed' && $this->unopenedRecipientsCount > 0,
            'allOpenedNotice' => $this->campaign->status === 'completed' && $this->metrics['total'] > 0 && $this->unopenedRecipientsCount === 0,
            'openRateSummary' => $this->openRateSummary(),
            'clickBreakdown' => $this->clickBreakdown(),
            'downloadBreakdown' => $this->downloadBreakdown(),
        ]);
    }

    private function refreshCampaign(): void
    {
        $this->campaign->load([
            'recipients.owner',
            'recipients.trackingEvents.document',
            'documents',
        ]);
    }

    private function refreshMetrics(): void
    {
        $recipients = $this->campaign->recipients;
        $openedRecipients = $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'open'))->count();

        $sentTotal = $this->campaign->channel === 'whatsapp'
            ? $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'whatsapp_sent'))->count()
            : $recipients->count();

        $this->metrics = [
            'total' => $sentTotal,
            'opens' => $openedRecipients,
            'clicks' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'click'))->count(),
            'downloads' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'download'))->count(),
            'failures' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->status === 'failed' || $recipient->trackingEvents->contains('event_type', 'error'))->count(),
        ];

        $this->unopenedRecipientsCount = max(0, $this->metrics['total'] - $openedRecipients);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recipientRows(): array
    {
        $rows = [];

        foreach ($this->campaign->recipients->sortByDesc('id') as $recipient) {
            $events = $recipient->trackingEvents->sortByDesc('created_at');
            $lastActivity = $events->first()?->created_at;
            $eventRows = [];

            foreach ($events as $event) {
                $eventRows[] = [
                    'id' => $event->id,
                    'type' => (string) $event->event_type,
                    'type_label' => __('campaigns.admin.event_types.' . $event->event_type),
                    'url' => $event->url,
                    'document_label' => $event->document?->filename,
                    'ip_address' => $event->ip_address,
                    'created_at' => $event->created_at,
                ];
            }

            $rows[] = [
                'id' => $recipient->id,
                'owner_id' => $recipient->owner?->id,
                'owner_edit_url' => $this->ownerEditUrl($recipient),
                'name' => $this->recipientName($recipient),
                'message_subject' => $recipient->message_subject,
                'contact' => $recipient->contact,
                'can_send_whatsapp' => $this->campaign->channel === 'whatsapp' && ! $this->isWhatsappContactBlocked($recipient),
                'whatsapp_sent' => $recipient->trackingEvents->contains('event_type', 'whatsapp_sent'),
                'whatsapp_blocked' => $this->campaign->channel === 'whatsapp' && $this->isWhatsappContactBlocked($recipient),
                'can_mark_manual_sent' => $this->campaign->channel === 'manual' && $recipient->status !== 'sent',
                'manual_sent' => $this->campaign->channel === 'manual' && $recipient->status === 'sent',
                'status' => $recipient->status,
                'status_label' => __('campaigns.admin.statuses.' . $recipient->status),
                'opened' => $recipient->trackingEvents->contains('event_type', 'open'),
                'clicks' => $recipient->trackingEvents->where('event_type', 'click')->count(),
                'downloads' => $recipient->trackingEvents->where('event_type', 'download')->count(),
                'last_activity' => $lastActivity,
                'events' => $eventRows,
            ];
        }

        return $rows;
    }

    private function openRateSummary(): string
    {
        if ($this->metrics['total'] === 0) {
            return '0 · 0,0%';
        }

        $percentage = number_format(($this->metrics['opens'] / $this->metrics['total']) * 100, 1, ',', '.');

        return $this->metrics['opens'] . '  ·  ' . $percentage . '%';
    }

    /**
     * @return array<int, array{label: string, count: int}>
     */
    private function clickBreakdown(): array
    {
        return $this->campaign->recipients
            ->flatMap(fn (CampaignRecipient $recipient) => $recipient->trackingEvents)
            ->where('event_type', 'click')
            ->filter(fn ($event): bool => filled($event->url))
            ->groupBy(fn ($event): string => (string) $event->url)
            ->map(fn ($events, string $url): array => [
                'label' => $url,
                'count' => $events->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, count: int}>
     */
    private function downloadBreakdown(): array
    {
        return $this->campaign->recipients
            ->flatMap(fn (CampaignRecipient $recipient) => $recipient->trackingEvents)
            ->where('event_type', 'download')
            ->filter(fn ($event): bool => filled($event->document?->filename))
            ->groupBy(fn ($event): string => (string) $event->document?->filename)
            ->map(fn ($events, string $filename): array => [
                'label' => $filename,
                'count' => $events->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    private function recipientName(CampaignRecipient $recipient): string
    {
        $owner = $recipient->owner;

        if ($owner === null) {
            return __('campaigns.admin.unknown_owner');
        }

        $name = $owner->fullName1;

        if ($recipient->slot === 'coprop2') {
            $name = $owner->fullName2 !== '' ? $owner->fullName2 : $owner->fullName1;
        }

        return $name ?: __('campaigns.admin.unknown_owner');
    }

    private function ownerEditUrl(CampaignRecipient $recipient): ?string
    {
        $owner = $recipient->owner;
        $user = $this->currentUser();

        if ($owner === null || $user === null || ! $user->hasRole(Role::SUPER_ADMIN)) {
            return null;
        }

        return route('admin.owners.index', ['editOwner' => $owner->id]);
    }

    private function authorizeViewAny(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('viewAny', Campaign::class);
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        /** @var User|null $user */
        return $user;
    }
}
