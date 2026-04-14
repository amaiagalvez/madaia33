<?php

namespace App\Services\Messaging;

use App\Mail\CampaignMail;
use App\Models\Setting;
use App\Models\CampaignDocument;
use App\Support\ConfiguredMailSettings;
use App\Models\CampaignRecipient;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Contracts\Messaging\EmailProvider;

class LaravelMailEmailProvider implements EmailProvider
{
    public function send(CampaignRecipient $recipient, string $subject, string $body): void
    {
        app(ConfiguredMailSettings::class)->apply(Setting::stringValues([
            'from_address',
            'from_name',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
        ]));

        $trackingPixelUrl = URL::to('/track/open/' . $recipient->tracking_token);

        $documents = $recipient->campaign
            ? $recipient->campaign->loadMissing('documents')->documents
            : collect();

        $documentLinks = $documents->map(
            fn(CampaignDocument $document): array => [
                'label' => $document->filename,
                'url' => route('tracking.document', [
                    'token' => $recipient->tracking_token,
                    'document' => $document,
                ]),
            ],
        );

        Mail::to($recipient->contact)->send(new CampaignMail(
            subjectText: $subject,
            htmlBody: $this->withTrackingLinks($body, $recipient->tracking_token),
            trackingPixelUrl: $trackingPixelUrl,
            documents: $documents,
            documentLinks: $documentLinks,
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
