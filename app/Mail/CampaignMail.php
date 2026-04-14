<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\CampaignDocument;
use App\Support\EmailLegalText;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly Collection $documentLinks;

    public readonly ?string $legalText;

    /**
     * @param  Collection<int, CampaignDocument>  $documents
     * @param  Collection<int, array{label: string, url: string}>|null  $documentLinks
     */
    public function __construct(
        public readonly string $subjectText,
        public readonly string $htmlBody,
        public readonly string $trackingPixelUrl,
        public readonly Collection $documents,
        ?Collection $documentLinks = null,
        ?string $legalText = null,
    ) {
        $this->documentLinks = $documentLinks ?? collect();
        $this->legalText = $legalText ?? EmailLegalText::resolve();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectText);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.campaign',
            with: [
                'htmlBody' => $this->htmlBody,
                'trackingPixelUrl' => $this->trackingPixelUrl,
                'documentLinks' => $this->documentLinks,
                'legalText' => $this->legalText,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return $this->documents
            ->map(fn(CampaignDocument $document): Attachment => Attachment::fromStorageDisk('public', $document->path)
                ->as($document->filename)
                ->withMime($document->mime_type))
            ->all();
    }
}
