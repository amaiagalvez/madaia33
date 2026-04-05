<?php

namespace App\Validations;

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
            'legalCheckboxTextEu' => 'nullable|string|max:1000',
            'legalCheckboxTextEs' => 'nullable|string|max:1000',
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
