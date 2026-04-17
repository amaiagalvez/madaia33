<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\BuildsFromAddress;

class OwnerWelcomeMail extends Mailable
{
    use BuildsFromAddress;
    use Queueable, SerializesModels;

    public readonly ?string $legalText;

    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly ?string $fromAddress,
        public readonly ?string $fromName,
        public readonly string $subjectLine,
        public readonly string $bodyHtml,
        public readonly string $resetUrl,
        public readonly ?string $trackingPixelUrl = null,
        public readonly ?string $trackedResetUrl = null,
    ) {
        $this->legalText = EmailLegalText::resolve();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            from: $this->buildFromAddress($this->fromAddress, $this->fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.owner-welcome',
            with: [
                'bodyHtml' => $this->bodyHtml,
                'resetUrl' => $this->resetUrl,
                'trackedResetUrl' => $this->trackedResetUrl,
                'legalText' => $this->legalText,
                'trackingPixelUrl' => $this->trackingPixelUrl,
            ],
        );
    }
}
