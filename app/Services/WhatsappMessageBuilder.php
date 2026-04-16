<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use Illuminate\Support\Collection;

class WhatsappMessageBuilder
{
    public function build(Campaign $campaign, CampaignRecipient $recipient): string
    {
        $content = $this->localizedBody($campaign, $recipient);
        $textBody = $this->plainText($content);
        $trackedLinks = $this->trackedBodyLinks($recipient, $content);
        $documentLinks = $this->trackedDocumentLinks($campaign, $recipient);
        $messageLines = [];

        if ($textBody !== '') {
            $messageLines[] = $textBody;
        }

        if ($trackedLinks->isNotEmpty()) {
            $messageLines[] = '';
            $messageLines[] = __('campaigns.admin.whatsapp.link_section');

            foreach ($trackedLinks as $link) {
                $messageLines[] = '- ' . $link;
            }
        }

        if ($documentLinks->isNotEmpty()) {
            $messageLines[] = '';
            $messageLines[] = __('campaigns.admin.whatsapp.documents_section');

            foreach ($documentLinks as $item) {
                $messageLines[] = '- ' . $item['label'] . ': ' . $item['url'];
            }
        }

        return trim(implode("\n", $messageLines));
    }

    private function localizedBody(Campaign $campaign, CampaignRecipient $recipient): string
    {
        $locale = $recipient->owner?->language;

        if ($locale === 'eu') {
            return (string) ($campaign->body_eu ?: $campaign->body_es ?: '');
        }

        if ($locale === 'es') {
            return (string) ($campaign->body_es ?: $campaign->body_eu ?: '');
        }

        return collect([$campaign->body_eu, $campaign->body_es])
            ->filter(fn (?string $v): bool => filled($v))
            ->implode("\n\n");
    }

    private function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $text));

        if (! is_string($text)) {
            return '';
        }

        return trim((string) preg_replace('/\n{3,}/u', "\n\n", $text));
    }

    /**
     * @return Collection<int, string>
     */
    private function trackedBodyLinks(CampaignRecipient $recipient, string $html): Collection
    {
        return $this->extractUrls($html)
            ->map(fn (string $url): string => route('tracking.click', [
                'token' => $recipient->tracking_token,
                'url' => $url,
            ]));
    }

    /**
     * @return Collection<int, array{label: string, url: string}>
     */
    private function trackedDocumentLinks(Campaign $campaign, CampaignRecipient $recipient): Collection
    {
        return $campaign->documents
            ->map(fn (CampaignDocument $document): array => [
                'label' => $document->filename,
                'url' => route('tracking.document', [
                    'token' => $recipient->tracking_token,
                    'document' => $document->id,
                ]),
            ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function extractUrls(string $html): Collection
    {
        $urls = collect();

        preg_match_all('/href=("|\')(.*?)\1/i', $html, $hrefMatches);
        preg_match_all('/https?:\/\/[^\s"\'<>]+/i', $html, $rawMatches);

        foreach ($hrefMatches[2] as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                $urls->push((string) $url);
            }
        }

        foreach ($rawMatches[0] as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                $urls->push((string) $url);
            }
        }

        return $urls->unique()->values();
    }
}
