<?php

namespace App\Support;

use Throwable;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

final class EmailLegalText
{
    public static function resolve(?string $fallback = null, ?string $locale = null): ?string
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $fallback;
            }
        } catch (Throwable) {
            return $fallback;
        }

        return Setting::localizedString('legal_text', $fallback, $locale);
    }
}
