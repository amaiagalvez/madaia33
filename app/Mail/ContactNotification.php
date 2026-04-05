<?php

namespace App\Mail;

class ContactNotification extends AbstractContactMail
{
    public function __construct(
        string $visitorName,
        public readonly string $visitorEmail,
        string $messageSubject,
        string $messageBody,
    ) {
        parent::__construct($visitorName, $messageSubject, $messageBody);
    }

    protected function viewName(): string
    {
        return 'mail.contact-notification';
    }
}
