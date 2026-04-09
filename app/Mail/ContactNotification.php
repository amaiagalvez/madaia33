<?php

namespace App\Mail;

use App\Models\ContactMessage;

class ContactNotification extends AbstractContactMail
{
    public readonly string $visitorEmail;

    public function __construct(ContactMessage $contactMessage)
    {
        $this->visitorEmail = $contactMessage->email;

        parent::__construct(
            visitorName: $contactMessage->name,
            messageSubject: $contactMessage->subject,
            messageBody: $contactMessage->message,
        );
    }

    protected function viewName(): string
    {
        return 'mail.contact-notification';
    }
}
