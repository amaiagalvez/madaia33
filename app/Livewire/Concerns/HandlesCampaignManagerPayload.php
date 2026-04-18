<?php

namespace App\Livewire\Concerns;

use App\Models\Campaign;
use Illuminate\Validation\Rule;
use App\Models\CampaignDocument;

trait HandlesCampaignManagerPayload
{
    /**
     * @return array<string, mixed>
     */
    private function campaignPayload(): array
    {
        return [
            ...$this->contentPayload(),
            'scheduled_at' => $this->scheduledAt !== null && $this->scheduledAt !== '' ? $this->scheduledAt : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templatePayload(): array
    {
        return [
            ...$this->contentPayload(),
            'name' => $this->templateName(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentPayload(): array
    {
        return [
            'subject_eu' => $this->normalizeNullableValue($this->subjectEu),
            'subject_es' => $this->normalizeNullableValue($this->subjectEs),
            'body_eu' => $this->normalizeNullableValue($this->bodyEu),
            'body_es' => $this->normalizeNullableValue($this->bodyEs),
            'channel' => $this->channel,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentRules(): array
    {
        return [
            'subjectEu' => ['nullable', 'string', 'max:255', 'required_without:subjectEs'],
            'subjectEs' => ['nullable', 'string', 'max:255', 'required_without:subjectEu'],
            'bodyEu' => ['nullable', 'string', 'required_without:bodyEs'],
            'bodyEs' => ['nullable', 'string', 'required_without:bodyEu'],
            'channel' => ['required', 'string', Rule::in(['email', 'sms', 'whatsapp', 'telegram', 'manual'])],
        ];
    }

    private function templateName(): string
    {
        $subject = $this->normalizeNullableValue($this->subjectEu)
            ?? $this->normalizeNullableValue($this->subjectEs);

        if ($subject !== null) {
            return mb_substr($subject, 0, 255);
        }

        return __('campaigns.admin.template') . ' ' . now()->format('Y-m-d H:i');
    }

    private function normalizeNullableValue(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function storeAttachments(Campaign $campaign): void
    {
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('campaign-documents/' . $campaign->id, 'public');

            CampaignDocument::query()->create([
                'campaign_id' => $campaign->id,
                'filename' => $attachment->getClientOriginalName(),
                'path' => $path,
                'mime_type' => (string) $attachment->getClientMimeType(),
                'size_bytes' => (int) $attachment->getSize(),
                'is_public' => false,
            ]);
        }
    }
}
