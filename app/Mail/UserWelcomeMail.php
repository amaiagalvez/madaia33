<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\BuildsFromAddress;

class UserWelcomeMail extends Mailable
{
    use BuildsFromAddress;
    use Queueable, SerializesModels;

    public readonly ?string $legalText;

    public function __construct(
        public readonly ?string $fromAddress,
        public readonly ?string $fromName,
        public readonly string $subjectLine,
        public readonly string $bodyHtml,
        public readonly ?string $recipientLocale = null,
    ) {
        $this->locale($this->recipientLocale);

        $this->legalText = EmailLegalText::resolve(locale: $this->recipientLocale);
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
            view: 'mail.user-welcome',
            with: [
                'bodyHtml' => $this->bodyHtml,
                'legalText' => $this->legalText,
            ],
        );
    }
}
