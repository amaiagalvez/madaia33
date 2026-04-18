<?php

namespace App\Support;

final class OwnerIdentitySanitizer
{
    public static function sanitizeDni(?string $value): string
    {
        $cleaned = preg_replace('/[^0-9A-Za-z]/', '', trim((string) $value));

        return strtoupper($cleaned ?? '');
    }

    public static function sanitizePhone(?string $value): string
    {
        return preg_replace('/[^0-9]/', '', trim((string) $value)) ?? '';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $dniFields
     * @param  array<int, string>  $phoneFields
     * @return array<string, mixed>
     */
    public static function sanitizeFields(array $payload, array $dniFields, array $phoneFields): array
    {
        foreach ($dniFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = self::sanitizeDni((string) $payload[$field]);
            }
        }

        foreach ($phoneFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = self::sanitizePhone((string) $payload[$field]);
            }
        }

        return $payload;
    }
}
