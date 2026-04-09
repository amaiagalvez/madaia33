<?php

namespace App\Mail;

use App\Models\ContactMessage;

class ContactNotification extends AbstractContactMail
{
    public readonly string $visitorEmail;

    public function __construct(
        ContactMessage $contactMessage,
        ?string $legalText = null,
        ?string $fromAddress = null,
        ?string $fromName = null,
    ) {
        $this->visitorEmail = $contactMessage->email;

        parent::__construct(
            visitorName: $contactMessage->name,
            messageSubject: $contactMessage->subject,
            messageBody: $contactMessage->message,
            legalText: $legalText,
            fromAddress: $fromAddress,
            fromName: $fromName,
        );
    }

    protected function viewName(): string
    {
        return 'mail.contact-notification';
    }
}
