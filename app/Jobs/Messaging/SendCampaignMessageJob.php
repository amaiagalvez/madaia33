<?php

namespace App\Jobs\Messaging;

use Throwable;
use RuntimeException;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use App\Contracts\Messaging\EmailProvider;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Messaging\MessageVariableResolver;

class SendCampaignMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $recipientId) {}

    /**
     * Execute the job.
     */
    public function handle(MessageVariableResolver $resolver, EmailProvider $emailProvider): void
    {
        $recipient = $this->loadRecipient();

        if ($recipient === null || $recipient->campaign === null || $recipient->owner === null) {
            return;
        }

        try {
            $this->sendMessage($recipient, $resolver, $emailProvider);
            $this->markDelivered($recipient);
        } catch (Throwable $exception) {
            $this->markFailed($recipient, $exception);
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

    private function markDelivered(CampaignRecipient $recipient): void
    {
        $recipient->status = 'sent';
        $recipient->error_message = null;
        $recipient->save();

        $this->recordTrackingEvent($recipient, 'delivered');
        $this->resetContactErrorState($recipient);
    }

    private function markFailed(CampaignRecipient $recipient, Throwable $exception): void
    {
        $recipient->status = 'failed';
        $recipient->error_message = $exception->getMessage();
        $recipient->save();

        $this->recordTrackingEvent($recipient, 'error');
        $this->increaseContactErrorState($recipient);
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

    private function resetContactErrorState(CampaignRecipient $recipient): void
    {
        $owner = $recipient->owner;

        if ($owner === null) {
            return;
        }

        $counterKey = $this->errorCounterField($recipient);
        $invalidKey = $this->invalidField($recipient);

        $owner->{$counterKey} = 0;
        $owner->{$invalidKey} = false;
        $owner->save();
    }

    private function increaseContactErrorState(CampaignRecipient $recipient): void
    {
        $owner = $recipient->owner;

        if ($owner === null) {
            return;
        }

        $counterKey = $this->errorCounterField($recipient);
        $invalidKey = $this->invalidField($recipient);

        $owner->{$counterKey} = (int) $owner->{$counterKey} + 1;
        $owner->last_contact_error_at = now();

        if ((int) $owner->{$counterKey} >= 3) {
            $owner->{$invalidKey} = true;
        }

        $owner->save();
    }

    private function errorCounterField(CampaignRecipient $recipient): string
    {
        $slotPrefix = $recipient->slot === 'coprop2' ? 'coprop2' : 'coprop1';

        if ($recipient->campaign->channel === 'email') {
            return $slotPrefix . '_email_error_count';
        }

        return $slotPrefix . '_phone_error_count';
    }

    private function invalidField(CampaignRecipient $recipient): string
    {
        $slotPrefix = $recipient->slot === 'coprop2' ? 'coprop2' : 'coprop1';

        if ($recipient->campaign->channel === 'email') {
            return $slotPrefix . '_email_invalid';
        }

        return $slotPrefix . '_phone_invalid';
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
