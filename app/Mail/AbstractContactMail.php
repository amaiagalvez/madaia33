<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

abstract class AbstractContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $visitorName,
        public readonly string $messageSubject,
        public readonly string $messageBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->messageSubject,
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
