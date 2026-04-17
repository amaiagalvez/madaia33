<?php

namespace App\Support\Messaging;

use Illuminate\Support\Facades\URL;

class CampaignTrackingUrlBuilder
{
  public function openPixelUrl(string $token): string
  {
    return URL::to('/track/open/' . $token);
  }

  public function trackedClickUrl(string $token, string $destination): string
  {
    return URL::to('/track/click/' . $token) . '?url=' . urlencode($destination);
  }

  public function withTrackedLinks(string $htmlBody, string $token): string
  {
    return (string) preg_replace_callback(
      '/href=(["\'])(.*?)\1/i',
      function (array $matches) use ($token): string {
        $quote = $matches[1];
        $destination = $matches[2];

        return 'href=' . $quote . $this->trackedClickUrl($token, $destination) . $quote;
      },
      $htmlBody,
    );
  }
}
