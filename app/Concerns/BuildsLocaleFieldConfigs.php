<?php

namespace App\Concerns;

use App\SupportedLocales;

trait BuildsLocaleFieldConfigs
{
    /**
     * @return array<string, array{field: string, fieldLabel: string, value: string}>
     */
    public function localeConfigsFor(string $propertyBase, string $labelKeyBase): array
    {
        return collect(SupportedLocales::all())
            ->mapWithKeys(function (string $locale) use ($propertyBase, $labelKeyBase): array {
                $suffix = SupportedLocales::propertySuffix($locale);
                $field = "{$propertyBase}{$suffix}";

                return [
                    $locale => [
                        'field' => $field,
                        'fieldLabel' => __("{$labelKeyBase}_{$locale}"),
                        'value' => (string) ($this->{$field} ?? ''),
                    ],
                ];
            })
            ->all();
    }
}
