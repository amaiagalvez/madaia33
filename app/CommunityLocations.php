<?php

namespace App;

final class CommunityLocations
{
    /** @var string[] */
    public const PORTALS = ['33-A', '33-B', '33-C', '33-D', '33-E', '33-F', '33-G', '33-H', '33-I', '33-J'];

    /** @var string[] */
    public const GARAGES = ['P-1', 'P-2', 'P-3'];

    public static function typeForCode(string $code): string
    {
        return in_array($code, self::PORTALS, true) ? 'portal' : 'garage';
    }

    /**
     * @return array<int, array{code: string, type: string, label: string}>
     */
    public static function options(string $portalLabel, string $garageLabel): array
    {
        return array_merge(
            array_map(
                static fn (string $code): array => ['code' => $code, 'type' => 'portal', 'label' => $portalLabel.' '.$code],
                self::PORTALS,
            ),
            array_map(
                static fn (string $code): array => ['code' => $code, 'type' => 'garage', 'label' => $garageLabel.' '.$code],
                self::GARAGES,
            ),
        );
    }
}
