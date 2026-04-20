<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class LocalizedDateFormatter
{
    public static function date(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '—';
        }

        return $date->format(self::datePattern());
    }

    public static function dateTime(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '—';
        }

        return $date->format(self::datePattern() . ' H:i:s');
    }

    public static function shortDateTime(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '—';
        }

        return $date->format(self::datePattern() . ' H:i');
    }

    private static function datePattern(): string
    {
        return app()->getLocale() === 'eu' ? 'Y/m/d' : 'd/m/Y';
    }
}
