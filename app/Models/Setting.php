<?php

namespace App\Models;

use App\SupportedLocales;
use Illuminate\Support\Facades\Cache;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory, SoftDeletes;

    private const STRING_VALUES_CACHE_KEY = 'settings:string-values';

    public const SECTION_CONTACT_FORM = 'contact_form';

    public const SECTION_EMAIL_CONFIGURATION = 'email_configuration';

    public const SECTION_FRONT = 'front';

    public const SECTION_GALLERY = 'gallery';

    public const SECTION_GENERAL = 'general';

    public const SECTION_RECAPTCHA = 'recaptcha';

    public const SECTION_OWNERS = 'owners';

    public const SECTION_VOTE_DELEGATE = 'vote_delegate';

    public const SECTION_VOTINGS = 'votings';

    protected $fillable = [
        'key',
        'value',
        'section',
    ];

    protected static function newFactory(): SettingFactory
    {
        return SettingFactory::new();
    }

    protected static function booted(): void
    {
        static::saved(static function (): void {
            self::flushStringValuesCache();
        });

        static::deleted(static function (): void {
            self::flushStringValuesCache();
        });

        static::restored(static function (): void {
            self::flushStringValuesCache();
        });

        static::forceDeleted(static function (): void {
            self::flushStringValuesCache();
        });
    }

    /**
     * @return list<string>
     */
    public static function allowedSections(): array
    {
        return [
            self::SECTION_CONTACT_FORM,
            self::SECTION_EMAIL_CONFIGURATION,
            self::SECTION_FRONT,
            self::SECTION_GALLERY,
            self::SECTION_GENERAL,
            self::SECTION_RECAPTCHA,
            self::SECTION_OWNERS,
            self::SECTION_VOTE_DELEGATE,
            self::SECTION_VOTINGS,
        ];
    }

    public static function normalizeSection(?string $section): string
    {
        return in_array($section, self::allowedSections(), true)
            ? (string) $section
            : self::SECTION_GENERAL;
    }

    public static function stringValue(string $key, string $default = ''): string
    {
        return (string) (self::allStringValues()[$key] ?? $default);
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    public static function stringValues(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $cachedSettings = self::allStringValues();
        $values = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $cachedSettings)) {
                $values[$key] = $cachedSettings[$key];
            }
        }

        return $values;
    }

    public static function localizedString(string $prefix, ?string $fallback = null, ?string $locale = null): ?string
    {
        $localizedKeys = SupportedLocales::localizedKeys($prefix, $locale);

        return self::localizedStringFrom(
            self::stringValues($localizedKeys),
            $prefix,
            $fallback,
            $locale,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function allStringValues(): array
    {
        /** @var array<string, string> $values */
        $values = Cache::rememberForever(self::STRING_VALUES_CACHE_KEY, static fn (): array => self::query()
            ->pluck('value', 'key')
            ->map(static fn (mixed $value): string => (string) $value)
            ->all());

        return $values;
    }

    public static function flushStringValuesCache(): void
    {
        Cache::forget(self::STRING_VALUES_CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    public static function refreshStringValuesCache(): array
    {
        self::flushStringValuesCache();

        return self::allStringValues();
    }

    /**
     * @param  array<string, string>  $settings
     */
    public static function localizedStringFrom(array $settings, string $prefix, ?string $fallback = null, ?string $locale = null): ?string
    {
        foreach (SupportedLocales::localizedKeys($prefix, $locale) as $key) {
            $value = trim((string) ($settings[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }
}
