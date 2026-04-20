<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use App\Models\ConstructionInquiry;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class ConstructionInquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ConstructionInquiry $inquiry)
    {
        $this->inquiry->loadMissing('construction');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('constructions.mail.reply_subject', [
                'construction' => $this->inquiry->construction->title,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.construction-inquiry-reply',
            with: [
                'inquiry' => $this->inquiry,
                'legalText' => EmailLegalText::resolve(),
            ],
        );
    }
}
