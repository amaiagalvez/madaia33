<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use App\Support\ContactMailData;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\BuildsFromAddress;

abstract class AbstractContactMail extends Mailable
{
    use BuildsFromAddress;
    use Queueable, SerializesModels;

    public readonly string $visitorName;

    public readonly string $messageSubject;

    public readonly string $messageBody;

    public readonly ?string $legalText;

    public readonly ?string $fromAddress;

    public readonly ?string $fromName;

    public function __construct(
        ContactMailData $mailData,
        ?string $fromAddress = null,
        ?string $fromName = null,
    ) {
        $this->visitorName = $mailData->visitorName;
        $this->messageSubject = $mailData->messageSubject;
        $this->messageBody = $mailData->messageBody;
        $this->legalText = $mailData->legalText ?? EmailLegalText::resolve();
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

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
