<?php

namespace App\Validations;

use App\Models\Setting;
use App\Rules\NoScriptTags;
use Illuminate\Validation\Rule;
use App\Support\ConfiguredMailSettings;

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
                'cookiePolicyContentEu' => ['nullable', 'string', new NoScriptTags],
                'cookiePolicyContentEs' => ['nullable', 'string', new NoScriptTags],
                'frontPhotoRequestTextEu' => ['nullable', 'string', 'max:1000', new NoScriptTags],
                'frontPhotoRequestTextEs' => ['nullable', 'string', 'max:1000', new NoScriptTags],
                'frontPrimaryEmail' => ['required', 'email', 'max:255'],
                'frontSiteName' => ['required', 'string', 'max:255'],
                'frontLogoImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'frontLogoImagePath' => ['nullable', 'string', 'max:500'],
            ],
            Setting::SECTION_CONTACT_FORM => [
                'adminEmail' => 'required|email|max:255',
                'legalCheckboxTextEu' => ['nullable', 'string', 'max:1000', new NoScriptTags],
                'legalCheckboxTextEs' => ['nullable', 'string', 'max:1000', new NoScriptTags],
                'contactFormSubjectEu' => ['nullable', 'string', 'max:255'],
                'contactFormSubjectEs' => ['nullable', 'string', 'max:255'],
            ],
            Setting::SECTION_EMAIL_CONFIGURATION => [
                'emailFromAddress' => 'required|email|max:255',
                'emailFromName' => 'nullable|string|max:255',
                'smtpHost' => 'required|string|max:255',
                'smtpPort' => 'required|integer|min:1|max:65535',
                'smtpUsername' => 'nullable|string|max:255|required_with:smtpPassword',
                'smtpPassword' => 'nullable|string|max:255|required_with:smtpUsername',
                'smtpEncryption' => ['nullable', 'string', Rule::in(ConfiguredMailSettings::encryptionOptions())],
                'emailLegalTextEu' => ['nullable', 'string', 'max:5000', new NoScriptTags],
                'emailLegalTextEs' => ['nullable', 'string', 'max:5000', new NoScriptTags],
            ],
            Setting::SECTION_RECAPTCHA => [
                'recaptchaSiteKey' => 'nullable|string|max:255',
                'recaptchaSecretKey' => 'nullable|string|max:255',
            ],
            Setting::SECTION_OWNERS => [
                'ownersWelcomeSubjectEu' => ['nullable', 'string', 'max:255'],
                'ownersWelcomeSubjectEs' => ['nullable', 'string', 'max:255'],
                'ownersWelcomeTextEu' => ['nullable', 'string', 'max:5000', new NoScriptTags],
                'ownersWelcomeTextEs' => ['nullable', 'string', 'max:5000', new NoScriptTags],
                'ownersTermsTextEu' => ['nullable', 'string', 'max:5000', new NoScriptTags],
                'ownersTermsTextEs' => ['nullable', 'string', 'max:5000', new NoScriptTags],
            ],
            default => [],
        };
    }
}
