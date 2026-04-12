<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\BuildsFromAddress;

class OwnerWelcomeMail extends Mailable
{
    use BuildsFromAddress;
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ?string $fromAddress,
        public readonly ?string $fromName,
        public readonly string $subjectLine,
        public readonly string $bodyHtml,
        public readonly string $resetUrl,
    ) {}

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
            ],
        );
    }
}
