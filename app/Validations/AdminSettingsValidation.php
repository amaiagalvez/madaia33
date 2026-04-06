<?php

namespace App\Validations;

use App\Rules\NoScriptTags;

class AdminSettingsValidation
{
    /**
     * @return array<string, string>
     */
    public static function rules(): array
    {
        return [
            'adminEmail' => 'required|email|max:255',
            'recaptchaSiteKey' => 'nullable|string|max:255',
            'recaptchaSecretKey' => 'nullable|string|max:255',
            'legalCheckboxTextEu' => ['nullable', 'string', 'max:1000', new NoScriptTags],
            'legalCheckboxTextEs' => ['nullable', 'string', 'max:1000', new NoScriptTags],
            'legalUrl' => 'nullable|url|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [];
    }
}
