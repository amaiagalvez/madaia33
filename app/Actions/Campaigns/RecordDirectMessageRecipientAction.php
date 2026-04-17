<?php

namespace App\Actions\Campaigns;

use App\Models\Owner;
use App\Models\Campaign;
use Illuminate\Support\Carbon;
use App\Models\CampaignRecipient;

class RecordDirectMessageRecipientAction
{
    private const DIRECT_MESSAGES_CAMPAIGN_ID = 1;

    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function execute(
        Owner $owner,
        string $contact,
        string $subject,
        string $body,
        ?int $sentByUserId = null,
        ?Carbon $sentAt = null,
    ): CampaignRecipient {
        $campaign = $this->resolveDirectMessagesCampaign();

        return CampaignRecipient::query()->create([
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'slot' => $this->resolveSlot($owner, $contact),
            'contact' => $contact,
            'tracking_token' => bin2hex(random_bytes(32)),
            'status' => 'sent',
            'message_subject' => $subject,
            'message_body' => $body,
            'sent_at' => $sentAt ?? now(),
            'sent_by_user_id' => $sentByUserId,
            'error_message' => null,
        ]);
    }

    private function resolveDirectMessagesCampaign(): Campaign
    {
        return Campaign::unguarded(function (): Campaign {
            return Campaign::query()->updateOrCreate(
                ['id' => self::DIRECT_MESSAGES_CAMPAIGN_ID],
                [
                    'created_by_user_id' => null,
                    'subject_eu' => 'Web-etik Bidalitako Mezuak',
                    'subject_es' => 'Mensajes enviados desde la web',
                    'body_eu' => null,
                    'body_es' => null,
                    'channel' => 'email',
                    'status' => 'sent',
                    'scheduled_at' => null,
                    'sent_at' => now(),
                ],
            );
        });
    }

    private function resolveSlot(Owner $owner, string $contact): string
    {
        if ($owner->coprop2_email !== null && strcasecmp($owner->coprop2_email, $contact) === 0) {
            return 'coprop2';
        }

        return 'coprop1';
    }
}
