<?php

namespace App\Models;

use App\SupportedLocales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory, SoftDeletes;

    public const SECTION_CONTACT_FORM = 'contact_form';

    public const SECTION_EMAIL_CONFIGURATION = 'email_configuration';

    public const SECTION_FRONT = 'front';

    public const SECTION_GALLERY = 'gallery';

    public const SECTION_GENERAL = 'general';

    public const SECTION_RECAPTCHA = 'recaptcha';

    public const SECTION_OWNERS = 'owners';

    protected $fillable = [
        'key',
        'value',
        'section',
    ];

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
        return (string) (self::query()->where('key', $key)->value('value') ?? $default);
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

        return self::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->map(static fn (mixed $value): string => (string) $value)
            ->all();
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
