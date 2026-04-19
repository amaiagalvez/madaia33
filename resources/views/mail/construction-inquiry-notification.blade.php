<x-mail.layout :legal-text="$legalText">
    <p style="margin: 0 0 16px 0;">{{ __('constructions.mail.notification_intro') }}</p>

    <p style="margin: 0 0 6px 0;"><strong>{{ __('constructions.mail.construction_label') }}:</strong>
        {{ $construction->title }}</p>
    <p style="margin: 0 0 6px 0;"><strong>{{ __('constructions.mail.sender_label') }}:</strong>
        {{ $inquiry->name }} ({{ $inquiry->email }})</p>
    <p style="margin: 0 0 6px 0;"><strong>{{ __('constructions.mail.subject_label') }}:</strong>
        {{ $inquiry->subject }}</p>
    <p style="margin: 0 0 8px 0;"><strong>{{ __('constructions.mail.message_label') }}:</strong></p>
    <p style="white-space: pre-line; margin: 0;">{{ $inquiry->message }}</p>
</x-mail.layout>
