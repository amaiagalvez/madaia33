<?php

namespace App\Validations;

use App\Rules\NoScriptTags;

class ContactFormValidation
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(string $siteKey): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => ['required', 'string', 'max:255', new NoScriptTags],
            'message' => ['required', 'string', 'max:5000', new NoScriptTags],
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
