<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\BuildsFromAddress;

class TestEmail extends Mailable
{
    use BuildsFromAddress;
    use Queueable, SerializesModels;

    public readonly ?string $legalText;

    public function __construct(
        public readonly ?string $fromAddress = null,
        public readonly ?string $fromName = null,
        ?string $legalText = null,
    ) {
        $this->legalText = $legalText ?? EmailLegalText::resolve();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('admin.test_email.subject'),
            from: $this->buildFromAddress($this->fromAddress, $this->fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.test-email',
            with: [
                'legalText' => $this->legalText,
            ],
        );
    }
}
