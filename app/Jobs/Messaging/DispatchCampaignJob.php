<?php

namespace App\Jobs\Messaging;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Messaging\RecipientResolver;

class DispatchCampaignJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $campaignId) {}

    /**
     * Execute the job.
     */
    public function handle(RecipientResolver $resolver): void
    {
        $campaign = Campaign::query()->find($this->campaignId);

        if ($campaign === null) {
            return;
        }

        $campaign->status = 'sending';
        $campaign->save();

        $existingRecipients = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->get();

        if ($existingRecipients->isNotEmpty()) {
            foreach ($existingRecipients as $recipient) {
                $recipient->status = 'pending';
                $recipient->error_message = null;

                if (! filled($recipient->tracking_token)) {
                    $recipient->tracking_token = bin2hex(random_bytes(32));
                }

                $recipient->save();

                dispatch(new SendCampaignMessageJob($recipient->id));
            }

            return;
        }

        $resolvedRecipients = $resolver->resolve($campaign);

        foreach ($resolvedRecipients as $resolvedRecipient) {
            $recipient = CampaignRecipient::query()->create([
                'campaign_id' => $campaign->id,
                'owner_id' => $resolvedRecipient['owner_id'],
                'slot' => $resolvedRecipient['slot'],
                'contact' => $resolvedRecipient['contact'],
                'tracking_token' => bin2hex(random_bytes(32)),
                'status' => 'pending',
                'error_message' => null,
            ]);

            dispatch(new SendCampaignMessageJob($recipient->id));
        }
    }
}
