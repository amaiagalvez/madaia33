<?php

namespace App\Livewire\Concerns;

use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use App\Services\WhatsappMessageBuilder;
use App\Support\Messaging\RecipientContactHealthManager;
use App\Support\Messaging\WhatsappClickToChatUrl;

trait HandlesCampaignDetailWhatsapp
{
    public function sendWhatsappMessage(int $recipientId): void
    {
        $this->authorizeViewAny();
        $this->authorize('view', $this->campaign);

        abort_unless($this->campaign->channel === 'whatsapp', 403);

        $result = $this->processWhatsappMessage($recipientId);

        if ($result['type'] === 'blocked' || $result['type'] === 'failed') {
            session()->flash('warning', $result['message']);

            return;
        }

        $this->dispatch('open-whatsapp', webUrl: $result['web_url']);

        session()->flash('message', $result['message']);
    }

    /**
     * @return array{type: string, message: string, web_url?: string}
     */
    private function processWhatsappMessage(int $recipientId): array
    {
        $recipient = $this->findWhatsappRecipient($recipientId);
        $contactHealthManager = app(RecipientContactHealthManager::class);

        if ($contactHealthManager->isBlocked($recipient)) {
            return [
                'type' => 'blocked',
                'message' => __('campaigns.admin.messages.whatsapp_contact_blocked'),
            ];
        }

        $whatsappUrls = $this->buildWhatsappUrls($recipient);

        if ($whatsappUrls === null) {
            $message = $this->markWhatsappFailure($recipient, __('campaigns.admin.messages.whatsapp_invalid_contact'), $contactHealthManager);

            return [
                'type' => 'failed',
                'message' => $message,
            ];
        }

        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => null,
            'event_type' => 'whatsapp_sent',
            'url' => $whatsappUrls['web'],
            'ip_address' => request()->ip(),
        ]);

        $recipient->status = 'sent';
        $recipient->error_message = null;
        $recipient->save();

        $contactHealthManager->markSuccess($recipient);

        return [
            'type' => 'sent',
            'message' => __('campaigns.admin.messages.whatsapp_marked_sent'),
            'web_url' => $whatsappUrls['web'],
        ];
    }

    private function findWhatsappRecipient(int $recipientId): CampaignRecipient
    {
        return CampaignRecipient::query()
            ->with(['campaign', 'owner'])
            ->where('campaign_id', $this->campaign->id)
            ->findOrFail($recipientId);
    }

    /**
     * @return array{web: string}|null
     */
    private function buildWhatsappUrls(CampaignRecipient $recipient): ?array
    {
        $message = $this->buildWhatsappMessage($recipient);

        if ($message === '') {
            return null;
        }

        $urlBuilder = app(WhatsappClickToChatUrl::class);
        $webUrl = $urlBuilder->buildWebUrl($recipient->contact, $message);

        if ($webUrl === null) {
            return null;
        }

        return [
            'web' => $webUrl,
        ];
    }

    private function markWhatsappFailure(
        CampaignRecipient $recipient,
        string $errorMessage,
        RecipientContactHealthManager $contactHealthManager,
    ): string {
        $recipient->status = 'failed';
        $recipient->error_message = $errorMessage;
        $recipient->save();

        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => null,
            'event_type' => 'error',
            'url' => null,
            'ip_address' => request()->ip(),
        ]);

        $contactHealthManager->markFailure($recipient);

        return $contactHealthManager->isBlocked($recipient)
            ? __('campaigns.admin.messages.whatsapp_contact_blocked')
            : $errorMessage;
    }

    private function isWhatsappContactBlocked(CampaignRecipient $recipient): bool
    {
        return app(RecipientContactHealthManager::class)->isBlocked($recipient);
    }

    private function buildWhatsappMessage(CampaignRecipient $recipient): string
    {
        return app(WhatsappMessageBuilder::class)->build($this->campaign, $recipient);
    }
}
