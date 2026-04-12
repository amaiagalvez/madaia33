<?php

namespace App\Models\Concerns;

use App\SupportedLocales;
use Illuminate\Support\Facades\App;

trait ResolvesLocalizedAttributes
{
    protected function resolveLocalizedAttribute(string $attributeBase): string
    {
        foreach (SupportedLocales::fallbackChain(App::getLocale()) as $locale) {
            $attribute = "{$attributeBase}_{$locale}";

            if (filled($this->{$attribute})) {
                return (string) $this->{$attribute};
            }
        }

        return '';
    }
}
