<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AdminCampaignDetail extends Component
{
    public Campaign $campaign;

    public ?int $expandedRecipientId = null;

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

        $this->campaign = $campaign;
        $this->refreshCampaign();
        $this->refreshMetrics();
    }

    public function toggleRecipientDetails(int $recipientId): void
    {
        $this->expandedRecipientId = $this->expandedRecipientId === $recipientId ? null : $recipientId;
    }

    public function render(): View
    {
        $this->refreshCampaign();
        $this->refreshMetrics();

        return view('livewire.admin.campaign-detail', [
            'recipientRows' => $this->recipientRows(),
        ]);
    }

    private function refreshCampaign(): void
    {
        $this->campaign->load([
            'recipients.owner',
            'recipients.trackingEvents.document',
        ]);
    }

    private function refreshMetrics(): void
    {
        $recipients = $this->campaign->recipients;

        $this->metrics = [
            'total' => $recipients->count(),
            'opens' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'open'))->count(),
            'clicks' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'click'))->count(),
            'downloads' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'download'))->count(),
            'failures' => $recipients->filter(fn (CampaignRecipient $recipient): bool => $recipient->status === 'failed' || $recipient->trackingEvents->contains('event_type', 'error'))->count(),
        ];
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
                    'ip_address' => $event->ip_address,
                    'created_at' => $event->created_at,
                ];
            }

            $rows[] = [
                'id' => $recipient->id,
                'name' => $this->recipientName($recipient),
                'contact' => $recipient->contact,
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

    private function recipientName(CampaignRecipient $recipient): string
    {
        $owner = $recipient->owner;

        if ($owner === null) {
            return __('campaigns.admin.unknown_owner');
        }

        $name = $recipient->slot === 'coprop2'
            ? ($owner->coprop2_name ?: $owner->coprop1_name)
            : $owner->coprop1_name;

        return $name ?: __('campaigns.admin.unknown_owner');
    }

    private function authorizeViewAny(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('viewAny', Campaign::class);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
