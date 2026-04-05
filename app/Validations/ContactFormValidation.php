<?php

namespace App\Actions;

namespace App\Validations;

class ContactFormValidation
{
    /**
     * @return array<string, string>
     */
    public static function rules(string $siteKey): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'legalAccepted' => 'accepted',
            'recaptchaToken' => $siteKey ? 'required|string' : 'nullable|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'name.required' => __('contact.validation.name_required'),
            'email.required' => __('contact.validation.email_required'),
            'email.email' => __('contact.validation.email_invalid'),
            'subject.required' => __('contact.validation.subject_required'),
            'message.required' => __('contact.validation.message_required'),
            'message.max' => __('contact.validation.message_max'),
            'legalAccepted.accepted' => __('contact.validation.legal_required'),
        ];
    }
}
