<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\BuildsFromAddress;

abstract class AbstractContactMail extends Mailable
{
    use BuildsFromAddress;
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $visitorName,
        public readonly string $messageSubject,
        public readonly string $messageBody,
        public readonly ?string $legalText = null,
        public readonly ?string $fromAddress = null,
        public readonly ?string $fromName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->messageSubject,
            from: $this->buildFromAddress($this->fromAddress, $this->fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->viewName(),
        );
    }

    abstract protected function viewName(): string;
}
