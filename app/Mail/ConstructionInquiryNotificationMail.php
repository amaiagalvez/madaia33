<?php

namespace App\Mail;

use App\Models\Construction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Support\EmailLegalText;
use App\Models\ConstructionInquiry;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class ConstructionInquiryNotificationMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public ConstructionInquiry $inquiry,
    public Construction $construction,
  ) {}

  public function envelope(): Envelope
  {
    return new Envelope(
      subject: __('constructions.mail.notification_subject', ['construction' => $this->construction->title]),
    );
  }

  public function content(): Content
  {
    return new Content(
      view: 'mail.construction-inquiry-notification',
      with: [
        'construction' => $this->construction,
        'inquiry' => $this->inquiry,
        'legalText' => EmailLegalText::resolve(),
      ],
    );
  }
}
