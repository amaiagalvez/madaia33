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
            Setting::SECTION_FRONT => self::frontRules(),
            Setting::SECTION_CONTACT_FORM => self::contactFormRules(),
            Setting::SECTION_EMAIL_CONFIGURATION => self::emailConfigurationRules(),
            Setting::SECTION_RECAPTCHA => self::recaptchaRules(),
            Setting::SECTION_OWNERS => self::ownerRules(),
            Setting::SECTION_VOTE_DELEGATE => self::voteDelegateRules(),
            Setting::SECTION_VOTINGS => self::votingsRules(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function frontRules(): array
    {
        return [
            'historyTextEu' => self::richTextRule(),
            'historyTextEs' => self::richTextRule(),
            'privacyContentEu' => self::richTextRule(),
            'privacyContentEs' => self::richTextRule(),
            'legalNoticeContentEu' => self::richTextRule(),
            'legalNoticeContentEs' => self::richTextRule(),
            'cookiePolicyContentEu' => self::richTextRule(),
            'cookiePolicyContentEs' => self::richTextRule(),
            'frontPhotoRequestTextEu' => self::richTextRule(1000),
            'frontPhotoRequestTextEs' => self::richTextRule(1000),
            'frontPrimaryEmail' => ['required', 'email', 'max:255'],
            'frontSiteName' => ['required', 'string', 'max:255'],
            'frontLogoImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'frontLogoImagePath' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function contactFormRules(): array
    {
        return [
            'adminEmail' => 'required|email|max:255',
            'legalCheckboxTextEu' => self::richTextRule(1000),
            'legalCheckboxTextEs' => self::richTextRule(1000),
            'contactFormSubjectEu' => ['nullable', 'string', 'max:255'],
            'contactFormSubjectEs' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function emailConfigurationRules(): array
    {
        return [
            'emailFromAddress' => 'required|email|max:255',
            'emailFromName' => 'nullable|string|max:255',
            'smtpHost' => 'required|string|max:255',
            'smtpPort' => 'required|integer|min:1|max:65535',
            'smtpUsername' => 'nullable|string|max:255|required_with:smtpPassword',
            'smtpPassword' => 'nullable|string|max:255|required_with:smtpUsername',
            'smtpEncryption' => ['nullable', 'string', Rule::in(ConfiguredMailSettings::encryptionOptions())],
            'emailLegalTextEu' => self::richTextRule(5000),
            'emailLegalTextEs' => self::richTextRule(5000),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function recaptchaRules(): array
    {
        return [
            'recaptchaSiteKey' => 'nullable|string|max:255',
            'recaptchaSecretKey' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function ownerRules(): array
    {
        return [
            'ownersWelcomeSubjectEu' => ['nullable', 'string', 'max:255'],
            'ownersWelcomeSubjectEs' => ['nullable', 'string', 'max:255'],
            'ownersWelcomeTextEu' => self::richTextRule(5000),
            'ownersWelcomeTextEs' => self::richTextRule(5000),
            'ownersTermsTextEu' => self::richTextRule(5000),
            'ownersTermsTextEs' => self::richTextRule(5000),
            'ownersSendWelcomeMail' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function voteDelegateRules(): array
    {
        return [
            'voteDelegateTermsTextEu' => self::richTextRule(5000),
            'voteDelegateTermsTextEs' => self::richTextRule(5000),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function votingsRules(): array
    {
        return [
            'votingsPdfDelegatedTextEu' => self::richTextRule(10000),
            'votingsPdfDelegatedTextEs' => self::richTextRule(10000),
            'votingsPdfInPersonTextEu' => self::richTextRule(10000),
            'votingsPdfInPersonTextEs' => self::richTextRule(10000),
            'votingsExplanationTextEu' => self::richTextRule(10000),
            'votingsExplanationTextEs' => self::richTextRule(10000),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function richTextRule(?int $max = null): array
    {
        $rules = ['nullable', 'string'];

        if ($max !== null) {
            $rules[] = 'max:' . $max;
        }

        $rules[] = new NoScriptTags;

        return $rules;
    }
}
