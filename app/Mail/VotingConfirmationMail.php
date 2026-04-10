<?php

namespace App\Mail;

use App\Models\Owner;
use App\Models\Voting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class VotingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Owner $owner,
        public readonly Voting $voting,
    ) {}

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
