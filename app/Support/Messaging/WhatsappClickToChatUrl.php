<?php

namespace App\Support\Messaging;

class WhatsappClickToChatUrl
{
  public function build(string $phone, string $message): ?string
  {
    $normalizedPhone = preg_replace('/\D+/', '', $phone);

    if (! is_string($normalizedPhone) || $normalizedPhone === '') {
      return null;
    }

    return 'https://wa.me/' . $normalizedPhone . '?text=' . rawurlencode($message);
  }
}
