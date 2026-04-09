<p>{{ __('contact.mail.greeting', ['name' => $visitorName]) }}</p>

<p>{{ __('contact.mail.confirmation_intro') }}</p>

<p><strong>{{ __('contact.subject') }}:</strong> {{ $messageSubject }}</p>

<p><strong>{{ __('contact.message') }}:</strong></p>
<p>{{ $messageBody }}</p>

<p>{{ __('contact.mail.confirmation_footer') }}</p>
