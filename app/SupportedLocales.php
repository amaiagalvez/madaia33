<?php

namespace App;

use Illuminate\Support\Facades\App as AppFacade;

final class SupportedLocales
{
    public const BASQUE = 'eu';

    public const SPANISH = 'es';

    public const DEFAULT = self::BASQUE;

    /**
     * @var array<string, array{switcher_label: string, admin_tab_translation_key: string, property_suffix: string}>
     */
    private const METADATA = [
        self::BASQUE => [
            'switcher_label' => 'EU',
            'admin_tab_translation_key' => 'EUS',
            'property_suffix' => 'Eu',
        ],
        self::SPANISH => [
            'switcher_label' => 'ES',
            'admin_tab_translation_key' => 'CAS',
            'property_suffix' => 'Es',
        ],
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::METADATA);
    }

    public static function current(): string
    {
        return self::normalize(AppFacade::getLocale());
    }

    public static function default(): string
    {
        return self::DEFAULT;
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, self::METADATA);
    }

    public static function normalize(?string $locale): string
    {
        $locale = (string) $locale;

        return self::isSupported($locale) ? $locale : self::default();
    }

    /**
     * @return list<string>
     */
    public static function fallbackChain(?string $locale = null): array
    {
        $normalizedLocale = self::normalize($locale ?? self::current());

        return array_values(array_unique([
            $normalizedLocale,
            ...array_filter(
                self::all(),
                static fn (string $supportedLocale): bool => $supportedLocale !== $normalizedLocale,
            ),
        ]));
    }

    public static function localizedKey(string $prefix, ?string $locale = null): string
    {
        return $prefix.'_'.self::normalize($locale ?? self::current());
    }

    /**
     * @return list<string>
     */
    public static function localizedKeys(string $prefix, ?string $locale = null): array
    {
        return array_map(
            static fn (string $supportedLocale): string => $prefix.'_'.$supportedLocale,
            self::fallbackChain($locale),
        );
    }

    public static function switcherLabel(string $locale): string
    {
        return self::metadata($locale)['switcher_label'];
    }

    public static function adminTabTranslationKey(string $locale): string
    {
        return self::metadata($locale)['admin_tab_translation_key'];
    }

    public static function propertySuffix(string $locale): string
    {
        return self::metadata($locale)['property_suffix'];
    }

    public static function routeName(string $baseName, ?string $locale = null): string
    {
        return $baseName.'.'.self::normalize($locale ?? self::current());
    }

    public static function baseRouteName(string $routeName): string
    {
        foreach (self::all() as $locale) {
            $suffix = '.'.$locale;
            if (str_ends_with($routeName, $suffix)) {
                return substr($routeName, 0, -strlen($suffix));
            }
        }

        return $routeName;
    }

    /**
     * @return array{switcher_label: string, admin_tab_translation_key: string, property_suffix: string}
     */
    private static function metadata(string $locale): array
    {
        return self::METADATA[self::normalize($locale)];
    }
}
