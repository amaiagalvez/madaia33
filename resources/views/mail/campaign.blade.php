<x-mail.layout :legal-text="$legalText">
    {!! $htmlBody !!}

    @if ($documentLinks->isNotEmpty())
        <div style="margin-top: 24px;">
            <p style="margin: 0 0 12px; font-weight: 700;">{{ __('campaigns.mail.documents') }}</p>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($documentLinks as $documentLink)
                    <li style="margin-bottom: 8px;">
                        <a href="{{ $documentLink['url'] }}"
                            style="color: #793d3d; text-decoration: underline;">
                            {{ $documentLink['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <img src="{{ $trackingPixelUrl }}" alt="" width="1" height="1"
        style="display: block; border: 0;" />
</x-mail.layout>
