@props([
    'legalText' => null,
    'trackingPixelUrl' => null,
])
@php($siteName = \App\Support\EmailSiteName::resolve())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $siteName }}</title>
</head>

<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #3b1f1f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin: 0; padding: 32px 0;">
        <tr>
            <td align="center" style="padding: 0 16px;">

                {{-- Card --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    border="0"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(121,61,61,0.10);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color: #9e5252; padding: 28px 32px 20px 32px;">
                            <p
                                style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; color: #ffffff; font-family: Arial, Helvetica, sans-serif;">
                                {{ $siteName }}
                            </p>
                        </td>
                    </tr>

                    {{-- Golden accent line --}}
                    <tr>
                        <td
                            style="background-color: #3b1f1f; height: 3px; font-size: 0; line-height: 0;">
                            &nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td
                            style="padding: 32px 32px 28px 32px; font-size: 15px; line-height: 24px; color: #3b1f1f; font-family: Arial, Helvetica, sans-serif;">
                            {{ $slot }}
                        </td>
                    </tr>

                    @if (!empty($legalText))
                        {{-- Legal divider --}}
                        <tr>
                            <td style="padding: 0 32px;">
                                <div
                                    style="border-top: 1px solid #edd2c7; font-size: 0; line-height: 0;">
                                    &nbsp;</div>
                            </td>
                        </tr>
                        {{-- Legal text --}}
                        <tr>
                            <td
                                style="padding: 16px 32px 24px 32px; font-size: 11px; line-height: 17px; color: #b9a7a5; font-family: Arial, Helvetica, sans-serif;">
                                {!! $legalText !!}
                            </td>
                        </tr>
                    @endif

                    @if (!empty($trackingPixelUrl))
                        <tr>
                            <td style="font-size: 0; line-height: 0;">
                                <img src="{{ $trackingPixelUrl }}" alt="" width="1"
                                    height="1"
                                    style="display:block; border:0; margin:0; padding:0;" />
                            </td>
                        </tr>
                    @endif

                </table>

                {{-- Footer spacer --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    border="0" style="max-width: 600px;">
                    <tr>
                        <td align="center"
                            style="padding: 16px 0 8px 0; font-size: 11px; color: #b9a7a5; font-family: Arial, Helvetica, sans-serif;">
                            {{ $siteName }}
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>
