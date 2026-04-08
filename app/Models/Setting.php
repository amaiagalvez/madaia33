<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory, SoftDeletes;

    public const SECTION_CONTACT_FORM = 'contact_form';

    public const SECTION_FRONT = 'front';

    public const SECTION_GALLERY = 'gallery';

    public const SECTION_GENERAL = 'general';

    public const SECTION_RECAPTCHA = 'recaptcha';

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
            self::SECTION_FRONT,
            self::SECTION_GALLERY,
            self::SECTION_GENERAL,
            self::SECTION_RECAPTCHA,
        ];
    }

    public static function normalizeSection(?string $section): string
    {
        return in_array($section, self::allowedSections(), true)
            ? (string) $section
            : self::SECTION_GENERAL;
    }
}
