<x-mail.layout :legal-text="$legalText">
    <p style="margin: 0 0 16px 0;">
        {{ __('contact.admin.reply_hello', ['name' => $contactMessage->name]) }}</p>

    <p style="margin: 0 0 8px 0;"><strong>{{ __('contact.admin.our_reply') }}:</strong></p>
    <p style="white-space: pre-line; margin: 0 0 24px 0;">{{ $reply->reply_body }}</p>

    <p style="margin: 0 0 4px 0;"><strong>{{ __('contact.admin.original_subject') }}:</strong>
        {{ $contactMessage->subject }}</p>
    <p style="white-space: pre-line; margin: 0 0 16px 0; color: #6b7280; font-size: 14px;">
        {{ $contactMessage->message }}</p>

    <p style="margin: 0;">{{ __('contact.admin.thank_you') }}</p>
</x-mail.layout>
