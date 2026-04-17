<?php

namespace App\Mail;

use App\Models\Owner;
use App\Models\Voting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class VotingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly ?string $legalText;

    public readonly ?string $trackingPixelUrl;

    public function __construct(
        public readonly Owner $owner,
        public readonly Voting $voting,
        ?string $legalText = null,
        ?string $trackingPixelUrl = null,
    ) {
        $this->legalText = $legalText ?? EmailLegalText::resolve();
        $this->trackingPixelUrl = $trackingPixelUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('votings.mail.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.voting-confirmation',
            with: [
                'trackingPixelUrl' => $this->trackingPixelUrl,
            ],
        );
    }
}
