<?php

namespace App\Support;

final class ContactMailData
{
    public function __construct(
        public readonly string $visitorName,
        public readonly string $messageSubject,
        public readonly string $messageBody,
        public readonly ?string $legalText = null,
    ) {}
}
