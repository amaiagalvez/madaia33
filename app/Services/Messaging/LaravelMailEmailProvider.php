<?php

namespace App\Services\Messaging;

use App\Mail\CampaignMail;
use App\Models\CampaignRecipient;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Contracts\Messaging\EmailProvider;

class LaravelMailEmailProvider implements EmailProvider
{
    public function send(CampaignRecipient $recipient, string $subject, string $body): void
    {
        $trackingPixelUrl = URL::to('/track/open/' . $recipient->tracking_token);

        $documents = $recipient->campaign
            ? $recipient->campaign->documents
            : collect();

        Mail::to($recipient->contact)->send(new CampaignMail(
            subjectText: $subject,
            htmlBody: $this->withTrackingLinks($body, $recipient->tracking_token),
            trackingPixelUrl: $trackingPixelUrl,
            documents: $documents,
        ));
    }

    private function withTrackingLinks(string $htmlBody, string $token): string
    {
        return (string) preg_replace_callback(
            '/href=(["\'])(.*?)\1/i',
            static function (array $matches) use ($token): string {
                $quote = $matches[1];
                $destination = $matches[2];
                $trackedUrl = URL::to('/track/click/' . $token) . '?url=' . urlencode($destination);

                return 'href=' . $quote . $trackedUrl . $quote;
            },
            $htmlBody,
        );
    }
}
