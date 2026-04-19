<x-mail.layout :legal-text="$legalText">
    <p style="margin: 0 0 16px 0;">{{ __('constructions.mail.reply_intro') }}</p>

    <p style="margin: 0 0 6px 0;"><strong>{{ __('constructions.mail.construction_label') }}:</strong>
        {{ $inquiry->construction->title }}</p>
    <p style="margin: 0 0 8px 0;"><strong>{{ __('constructions.mail.reply_label') }}:</strong></p>
    <p style="white-space: pre-line; margin: 0 0 24px 0;">{{ $inquiry->reply }}</p>

    <p style="margin: 0 0 6px 0;"><strong>{{ __('constructions.mail.subject_label') }}:</strong>
        {{ $inquiry->subject }}</p>
    <p style="white-space: pre-line; margin: 0; color: #6b7280; font-size: 14px;">
        {{ $inquiry->message }}</p>
</x-mail.layout>
