<?php

namespace App\Validations;

use App\Models\Setting;
use App\Rules\NoScriptTags;
use Illuminate\Validation\Rule;

class AdminSettingsValidation
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'adminEmail' => 'required|email|max:255',
            'recaptchaSiteKey' => 'nullable|string|max:255',
            'recaptchaSecretKey' => 'nullable|string|max:255',
            'legalCheckboxTextEu' => ['nullable', 'string', 'max:1000', new NoScriptTags],
            'legalCheckboxTextEs' => ['nullable', 'string', 'max:1000', new NoScriptTags],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sectionRules(): array
    {
        return [
            'section' => ['required', 'string', Rule::in(Setting::allowedSections())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesBySection(string $section): array
    {
        return match ($section) {
            Setting::SECTION_FRONT => [
                'historyTextEu' => ['nullable', 'string', new NoScriptTags],
                'historyTextEs' => ['nullable', 'string', new NoScriptTags],
                'privacyContentEu' => ['nullable', 'string', new NoScriptTags],
                'privacyContentEs' => ['nullable', 'string', new NoScriptTags],
                'legalNoticeContentEu' => ['nullable', 'string', new NoScriptTags],
                'legalNoticeContentEs' => ['nullable', 'string', new NoScriptTags],
            ],
            Setting::SECTION_CONTACT_FORM => [
                'adminEmail' => 'required|email|max:255',
                'legalCheckboxTextEu' => ['nullable', 'string', 'max:1000', new NoScriptTags],
                'legalCheckboxTextEs' => ['nullable', 'string', 'max:1000', new NoScriptTags],
            ],
            Setting::SECTION_RECAPTCHA => [
                'recaptchaSiteKey' => 'nullable|string|max:255',
                'recaptchaSecretKey' => 'nullable|string|max:255',
            ],
            default => [],
        };
    }
}
