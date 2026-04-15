<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CampaignTrackingEvent;
use App\Jobs\Messaging\SendCampaignMessageJob;
use App\Support\Messaging\WhatsappClickToChatUrl;
use App\Actions\Campaigns\DuplicateCampaignAction;

class AdminCampaignDetail extends Component
{
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

    public function resendToUnopened(): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $this->campaign);

        abort_unless($this->campaign->status === 'completed', 403);

        $unopenedRecipientIds = $this->campaign
            ->recipients
            ->filter(fn(CampaignRecipient $recipient): bool => ! $recipient->trackingEvents->contains('event_type', 'open'))
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

    public function markWhatsappSent(int $recipientId): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $this->campaign);

        abort_unless($this->campaign->channel === 'whatsapp', 403);

        $recipient = CampaignRecipient::query()
            ->where('campaign_id', $this->campaign->id)
            ->findOrFail($recipientId);

        $whatsappUrl = $this->buildWhatsappUrl($recipient);

        if ($whatsappUrl === null) {
            session()->flash('warning', __('campaigns.admin.messages.whatsapp_invalid_contact'));

            return;
        }

        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => null,
            'event_type' => 'whatsapp_sent',
            'url' => $whatsappUrl,
            'ip_address' => request()->ip(),
        ]);

        $recipient->status = 'sent';
        $recipient->error_message = null;
        $recipient->save();

        session()->flash('message', __('campaigns.admin.messages.whatsapp_marked_sent'));
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
        $openedRecipients = $recipients->filter(fn(CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'open'))->count();

        $this->metrics = [
            'total' => $recipients->count(),
            'opens' => $openedRecipients,
            'clicks' => $recipients->filter(fn(CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'click'))->count(),
            'downloads' => $recipients->filter(fn(CampaignRecipient $recipient): bool => $recipient->trackingEvents->contains('event_type', 'download'))->count(),
            'failures' => $recipients->filter(fn(CampaignRecipient $recipient): bool => $recipient->status === 'failed' || $recipient->trackingEvents->contains('event_type', 'error'))->count(),
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
                'contact' => $recipient->contact,
                'whatsapp_url' => $this->campaign->channel === 'whatsapp' ? $this->buildWhatsappUrl($recipient) : null,
                'whatsapp_sent' => $recipient->trackingEvents->contains('event_type', 'whatsapp_sent'),
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
            ->flatMap(fn(CampaignRecipient $recipient) => $recipient->trackingEvents)
            ->where('event_type', 'click')
            ->filter(fn($event): bool => filled($event->url))
            ->groupBy(fn($event): string => (string) $event->url)
            ->map(fn($events, string $url): array => [
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
            ->flatMap(fn(CampaignRecipient $recipient) => $recipient->trackingEvents)
            ->where('event_type', 'download')
            ->filter(fn($event): bool => filled($event->document?->filename))
            ->groupBy(fn($event): string => (string) $event->document?->filename)
            ->map(fn($events, string $filename): array => [
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

        $name = $owner->coprop1_name;

        if ($recipient->slot === 'coprop2') {
            $name = $owner->coprop2_name ?: $owner->coprop1_name;
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

    private function buildWhatsappUrl(CampaignRecipient $recipient): ?string
    {
        $message = $this->buildWhatsappMessage($recipient);

        if ($message === '') {
            return null;
        }

        return app(WhatsappClickToChatUrl::class)->build($recipient->contact, $message);
    }

    private function buildWhatsappMessage(CampaignRecipient $recipient): string
    {
        $content = $this->localizedCampaignBody($recipient);
        $textBody = $this->plainTextFromHtml($content);
        $trackedLinks = $this->trackedBodyLinks($recipient, $content);
        $documentLinks = $this->trackedDocumentLinks($recipient);
        $messageLines = [];

        if ($textBody !== '') {
            $messageLines[] = $textBody;
        }

        if ($trackedLinks->isNotEmpty()) {
            $messageLines[] = '';
            $messageLines[] = __('campaigns.admin.whatsapp.link_section');

            foreach ($trackedLinks as $link) {
                $messageLines[] = '- ' . $link;
            }
        }

        if ($documentLinks->isNotEmpty()) {
            $messageLines[] = '';
            $messageLines[] = __('campaigns.admin.whatsapp.documents_section');

            foreach ($documentLinks as $item) {
                $messageLines[] = '- ' . $item['label'] . ': ' . $item['url'];
            }
        }

        return trim(implode("\n", $messageLines));
    }

    private function localizedCampaignBody(CampaignRecipient $recipient): string
    {
        $campaign = $this->campaign;
        $locale = $recipient->owner?->language;

        if ($locale === 'eu') {
            return $this->fallbackLocalizedValue($campaign->body_eu, $campaign->body_es);
        }

        if ($locale === 'es') {
            return $this->fallbackLocalizedValue($campaign->body_es, $campaign->body_eu);
        }

        return $this->mergeBothLocales($campaign->body_eu, $campaign->body_es);
    }

    private function fallbackLocalizedValue(?string $primary, ?string $fallback): string
    {
        return (string) ($primary ?: $fallback ?: '');
    }

    private function mergeBothLocales(?string $eu, ?string $es): string
    {
        return collect([$eu, $es])
            ->filter(fn(?string $value): bool => filled($value))
            ->implode("\n\n");
    }

    private function plainTextFromHtml(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $text));

        if (! is_string($text)) {
            return '';
        }

        return trim((string) preg_replace('/\n{3,}/u', "\n\n", $text));
    }

    /**
     * @return Collection<int, string>
     */
    private function trackedBodyLinks(CampaignRecipient $recipient, string $html): Collection
    {
        return $this->extractUrlsFromBody($html)
            ->map(fn(string $url): string => route('tracking.click', [
                'token' => $recipient->tracking_token,
                'url' => $url,
            ]));
    }

    /**
     * @return Collection<int, array{label: string, url: string}>
     */
    private function trackedDocumentLinks(CampaignRecipient $recipient): Collection
    {
        return $this->campaign->documents
            ->map(fn(CampaignDocument $document): array => [
                'label' => $document->filename,
                'url' => route('tracking.document', [
                    'token' => $recipient->tracking_token,
                    'document' => $document->id,
                ]),
            ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function extractUrlsFromBody(string $html): Collection
    {
        $urls = collect();

        preg_match_all('/href=("|\')(.*?)\1/i', $html, $hrefMatches);
        preg_match_all('/https?:\/\/[^\s"\'<>]+/i', $html, $rawMatches);

        foreach ($hrefMatches[2] as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                $urls->push((string) $url);
            }
        }

        foreach ($rawMatches[0] as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                $urls->push((string) $url);
            }
        }

        return $urls->unique()->values();
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
