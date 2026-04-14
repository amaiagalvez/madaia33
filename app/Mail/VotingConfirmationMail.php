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

    public function __construct(
        public readonly Owner $owner,
        public readonly Voting $voting,
        ?string $legalText = null,
    ) {
        $this->legalText = $legalText ?? EmailLegalText::resolve();
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
        );
    }
}
