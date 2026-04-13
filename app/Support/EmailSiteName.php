<?php

namespace App\Support;

use Throwable;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

final class EmailSiteName
{
  public static function resolve(): string
  {
    $fallback = (string) config('app.name');

    try {
      if (! Schema::hasTable('settings')) {
        return $fallback;
      }
    } catch (Throwable) {
      return $fallback;
    }

    return trim(Setting::stringValue('front_site_name', $fallback)) ?: $fallback;
  }
}
