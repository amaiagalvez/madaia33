<?php

namespace App\Jobs\Messaging;

use Throwable;
use RuntimeException;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use App\Contracts\Messaging\EmailProvider;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;
use App\Services\Messaging\MessageVariableResolver;
use App\Support\Messaging\RecipientContactHealthManager;

class SendCampaignMessageJob implements ShouldQueue
{
    use Queueable;

    private const EMAIL_SEND_RATE_LIMITER = 'campaign-email-send';

    public function __construct(public readonly int $recipientId) {}

    /**
     * @return array<int, RateLimited>
     */
    public function middleware(): array
    {
        return [new RateLimited(self::EMAIL_SEND_RATE_LIMITER)];
    }

    /**
     * Execute the job.
     */
    public function handle(
        MessageVariableResolver $resolver,
        EmailProvider $emailProvider,
        RecipientContactHealthManager $contactHealthManager,
    ): void {
        $recipient = $this->loadRecipient();

        if ($recipient === null || $recipient->campaign === null || $recipient->owner === null) {
            return;
        }

        try {
            $this->sendMessage($recipient, $resolver, $emailProvider);
            $this->markDelivered($recipient, $contactHealthManager);
        } catch (Throwable $exception) {
            $this->markFailed($recipient, $exception, $contactHealthManager);
        }

        $this->completeCampaignWhenAllRecipientsProcessed($recipient);
    }

    private function loadRecipient(): ?CampaignRecipient
    {
        return CampaignRecipient::query()
            ->with(['campaign', 'owner'])
            ->find($this->recipientId);
    }

    private function sendMessage(CampaignRecipient $recipient, MessageVariableResolver $resolver, EmailProvider $emailProvider): void
    {
        [$localizedSubject, $localizedBody] = $this->localizedCampaignContent($recipient);

        $subject = $resolver->resolve($localizedSubject, $recipient->owner, $recipient->slot);
        $body = $resolver->resolve($localizedBody, $recipient->owner, $recipient->slot);

        if ($recipient->campaign->channel !== 'email') {
            throw new RuntimeException(__('campaigns.errors.unsupported_channel_provider'));
        }

        $emailProvider->send($recipient, $subject, $body);
    }

    private function markDelivered(CampaignRecipient $recipient, RecipientContactHealthManager $contactHealthManager): void
    {
        $recipient->status = 'sent';
        $recipient->error_message = null;
        $recipient->save();

        $this->recordTrackingEvent($recipient, 'delivered');
        $contactHealthManager->markSuccess($recipient);
    }

    private function markFailed(
        CampaignRecipient $recipient,
        Throwable $exception,
        RecipientContactHealthManager $contactHealthManager,
    ): void {
        $recipient->status = 'failed';
        $recipient->error_message = $exception->getMessage();
        $recipient->save();

        $this->recordTrackingEvent($recipient, 'error');
        $contactHealthManager->markFailure($recipient);
    }

    private function recordTrackingEvent(CampaignRecipient $recipient, string $eventType): void
    {
        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => null,
            'event_type' => $eventType,
            'url' => null,
            'ip_address' => null,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function localizedCampaignContent(CampaignRecipient $recipient): array
    {
        $campaign = $recipient->campaign;
        $locale = $recipient->owner->language;

        if ($locale === 'eu') {
            return [
                $this->fallbackLocalizedValue($campaign->subject_eu, $campaign->subject_es),
                $this->fallbackLocalizedValue($campaign->body_eu, $campaign->body_es),
            ];
        }

        if ($locale === 'es') {
            return [
                $this->fallbackLocalizedValue($campaign->subject_es, $campaign->subject_eu),
                $this->fallbackLocalizedValue($campaign->body_es, $campaign->body_eu),
            ];
        }

        return [
            $this->mergeBothLocales($campaign->subject_eu, $campaign->subject_es),
            $this->mergeBothLocales($campaign->body_eu, $campaign->body_es),
        ];
    }

    private function fallbackLocalizedValue(?string $primary, ?string $fallback): string
    {
        return (string) ($primary ?: $fallback ?: '');
    }

    private function mergeBothLocales(?string $eu, ?string $es): string
    {
        return collect([$eu, $es])
            ->filter(fn (?string $value): bool => filled($value))
            ->implode("\n\n");
    }

    private function completeCampaignWhenAllRecipientsProcessed(CampaignRecipient $recipient): void
    {
        $campaign = $recipient->campaign;

        if ($campaign === null) {
            return;
        }

        $pendingRecipients = CampaignRecipient::query()
            ->where('campaign_id', $recipient->campaign_id)
            ->whereNotIn('status', ['sent', 'failed'])
            ->exists();

        if ($pendingRecipients) {
            return;
        }

        $campaign->status = 'completed';
        $campaign->sent_at = now();
        $campaign->save();
    }
}
