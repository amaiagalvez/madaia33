<?php

namespace App\Support\Messaging;

use App\Models\CampaignRecipient;

class RecipientContactHealthManager
{
    public function markSuccess(CampaignRecipient $recipient): void
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

    public function markFailure(CampaignRecipient $recipient): void
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

    public function isBlocked(CampaignRecipient $recipient): bool
    {
        $owner = $recipient->owner;

        if ($owner === null) {
            return false;
        }

        return (bool) $owner->{$this->invalidField($recipient)};
    }

    private function errorCounterField(CampaignRecipient $recipient): string
    {
        $slotPrefix = $recipient->slot === 'coprop2' ? 'coprop2' : 'coprop1';

        if ($recipient->campaign?->channel === 'email') {
            return $slotPrefix . '_email_error_count';
        }

        return $slotPrefix . '_phone_error_count';
    }

    private function invalidField(CampaignRecipient $recipient): string
    {
        $slotPrefix = $recipient->slot === 'coprop2' ? 'coprop2' : 'coprop1';

        if ($recipient->campaign?->channel === 'email') {
            return $slotPrefix . '_email_invalid';
        }

        return $slotPrefix . '_phone_invalid';
    }
}
