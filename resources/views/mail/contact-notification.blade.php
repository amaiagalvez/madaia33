<p>{{ __('contact.mail.notification_intro') }}</p>

<p><strong>{{ __('contact.name') }}:</strong> {{ $visitorName }}</p>
<p><strong>{{ __('contact.email') }}:</strong> {{ $visitorEmail }}</p>
<p><strong>{{ __('contact.subject') }}:</strong> {{ $messageSubject }}</p>

<p><strong>{{ __('contact.message') }}:</strong></p>
<p>{{ $messageBody }}</p>

@if ($legalText)
    <hr>
    <div>{!! $legalText !!}</div>
@endif
