<?php

namespace App\Support;

use App\SupportedLocales;

class ContactConfirmationSubject
{
    public static function forAudit(string $subject): string
    {
        $prefix = SupportedLocales::current() === SupportedLocales::SPANISH
            ? 'Confirmación'
            : 'Konfirmazioa';

        return '[' . $prefix . '] ' . $subject;
    }
}