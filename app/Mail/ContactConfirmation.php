<?php

namespace App\Mail;

class ContactConfirmation extends AbstractContactMail
{
    protected function viewName(): string
    {
        return 'mail.contact-confirmation';
    }
}
