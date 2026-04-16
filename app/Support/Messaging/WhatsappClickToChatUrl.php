<?php

namespace App\Support\Messaging;

class WhatsappClickToChatUrl
{
    public function build(string $phone, string $message): ?string
    {
        return $this->buildWebUrl($phone, $message);
    }

    public function buildWebUrl(string $phone, string $message): ?string
    {
        $normalizedPhone = $this->normalizePhone($phone);

        if ($normalizedPhone === null) {
            return null;
        }

        return 'https://wa.me/' . $normalizedPhone . '?text=' . rawurlencode($message);
    }

    private function normalizePhone(string $phone): ?string
    {
        $normalizedPhone = preg_replace('/\D+/', '', $phone);

        if (! is_string($normalizedPhone) || $normalizedPhone === '') {
            return null;
        }

        return $normalizedPhone;
    }
}
