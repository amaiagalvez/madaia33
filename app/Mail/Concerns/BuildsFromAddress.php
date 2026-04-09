<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Address;

trait BuildsFromAddress
{
    protected function buildFromAddress(?string $fromAddress, ?string $fromName): ?Address
    {
        if (! $fromAddress) {
            return null;
        }

        return new Address($fromAddress, $fromName ?? '');
    }
}
