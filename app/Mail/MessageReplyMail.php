<?php

namespace App\Mail;

use App\Models\MessageReply;
use App\Support\EmailLegalText;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class MessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public MessageReply $reply)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RE: ' . $this->reply->message->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.message-reply',
            with: [
                'reply' => $this->reply,
                'contactMessage' => $this->reply->message,
                'legalText' => EmailLegalText::resolve(),
            ],
        );
    }
}
