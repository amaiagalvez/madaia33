<x-mail.layout :legal-text="$legalText">
    <p style="margin: 0 0 16px 0;">{{ __('contact.mail.greeting', ['name' => $visitorName]) }}</p>

    <p style="margin: 0 0 16px 0;">{{ __('contact.mail.confirmation_intro') }}</p>

    <p style="margin: 0 0 12px 0;"><strong>{{ __('contact.subject') }}:</strong>
        {{ $messageSubject }}</p>

    <p style="margin: 0 0 8px 0;"><strong>{{ __('contact.message') }}:</strong></p>
    <p style="margin: 0 0 16px 0;">{{ $messageBody }}</p>

    <p style="margin: 0;">{{ __('contact.mail.confirmation_footer') }}</p>
</x-mail.layout>
