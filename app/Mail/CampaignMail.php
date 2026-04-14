<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\CampaignDocument;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, CampaignDocument>  $documents
     */
    public function __construct(
        public readonly string $subjectText,
        public readonly string $htmlBody,
        public readonly string $trackingPixelUrl,
        public readonly Collection $documents,
    ) {}

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
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return $this->documents
            ->map(fn (CampaignDocument $document): Attachment => Attachment::fromStorageDisk('public', $document->path)
                ->as($document->filename)
                ->withMime($document->mime_type))
            ->all();
    }
}
