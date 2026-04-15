<?php

namespace App\Actions\Campaigns;

use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use Illuminate\Support\Collection;

class DuplicateCampaignAction
{
    /**
     * @param  Collection<int, CampaignRecipient>|null  $manualRecipients
     */
    public function execute(Campaign $sourceCampaign, User $user, ?Collection $manualRecipients = null): Campaign
    {
        $newCampaign = Campaign::query()->create([
            'created_by_user_id' => $user->id,
            'subject_eu' => $sourceCampaign->subject_eu,
            'subject_es' => $sourceCampaign->subject_es,
            'body_eu' => $sourceCampaign->body_eu,
            'body_es' => $sourceCampaign->body_es,
            'channel' => $sourceCampaign->channel,
            'recipient_filter' => $sourceCampaign->recipient_filter,
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
        ]);

        $this->duplicateDocuments($sourceCampaign, $newCampaign);
        $this->duplicateManualRecipients($manualRecipients, $newCampaign);

        return $newCampaign;
    }

    private function duplicateDocuments(Campaign $sourceCampaign, Campaign $newCampaign): void
    {
        $documentsPayload = $sourceCampaign->documents
            ->map(static fn (CampaignDocument $document): array => [
                'campaign_id' => $newCampaign->id,
                'filename' => $document->filename,
                'path' => $document->path,
                'mime_type' => $document->mime_type,
                'size_bytes' => $document->size_bytes,
                'is_public' => $document->is_public,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($documentsPayload !== []) {
            CampaignDocument::query()->insert($documentsPayload);
        }
    }

    /**
     * @param  Collection<int, CampaignRecipient>|null  $manualRecipients
     */
    private function duplicateManualRecipients(?Collection $manualRecipients, Campaign $newCampaign): void
    {
        if ($manualRecipients === null || $manualRecipients->isEmpty()) {
            return;
        }

        $recipientsPayload = $manualRecipients
            ->map(static fn (CampaignRecipient $recipient): array => [
                'campaign_id' => $newCampaign->id,
                'owner_id' => $recipient->owner_id,
                'slot' => $recipient->slot,
                'contact' => $recipient->contact,
                'tracking_token' => bin2hex(random_bytes(32)),
                'status' => 'pending',
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        CampaignRecipient::query()->insert($recipientsPayload);
    }
}
