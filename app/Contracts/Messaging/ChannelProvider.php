<?php

namespace App\Contracts\Messaging;

use App\Models\CampaignRecipient;

interface ChannelProvider
{
    public function send(CampaignRecipient $recipient, string $subject, string $body): void;
}
