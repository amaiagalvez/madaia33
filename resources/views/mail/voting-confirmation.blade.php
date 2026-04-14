<x-mail.layout :legal-text="$legalText">
    <p style="margin: 0 0 16px 0;">
        {{ __('votings.mail.greeting', ['name' => $owner->coprop1_name]) }}</p>

    <p style="margin: 0 0 16px 0;">{{ __('votings.mail.body', ['voting' => $voting->name]) }}</p>

    <p style="margin: 0;">{{ __('votings.mail.thanks') }}</p>
</x-mail.layout>
