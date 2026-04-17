<?php

namespace App\Actions\Campaigns;

use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignLocation;

class DuplicateCampaignAction
{
    public function execute(Campaign $sourceCampaign, User $user): Campaign
    {
        $newCampaign = Campaign::query()->create([
            'created_by_user_id' => $user->id,
            'subject_eu' => $sourceCampaign->subject_eu,
            'subject_es' => $sourceCampaign->subject_es,
            'body_eu' => $sourceCampaign->body_eu,
            'body_es' => $sourceCampaign->body_es,
            'channel' => $sourceCampaign->channel,
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
        ]);

        $this->duplicateDocuments($sourceCampaign, $newCampaign);
        $this->duplicateLocations($sourceCampaign, $newCampaign);

        return $newCampaign;
    }

    private function duplicateLocations(Campaign $sourceCampaign, Campaign $newCampaign): void
    {
        $locationsPayload = CampaignLocation::query()
            ->where('campaign_id', $sourceCampaign->id)
            ->whereNull('deleted_at')
            ->get()
            ->map(static fn (CampaignLocation $location): array => [
                'campaign_id' => $newCampaign->id,
                'location_id' => $location->location_id,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ])
            ->all();

        if ($locationsPayload !== []) {
            CampaignLocation::query()->insert($locationsPayload);
        }
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
}
