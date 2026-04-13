@props([
    'legalText' => null,
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

<body
    style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color: #f5f5f5; margin: 0; padding: 24px 0;">
        <tr>
            <td align="center" style="padding: 0 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    border="0"
                    style="max-width: 640px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td
                            style="padding: 24px 24px 12px 24px; font-size: 22px; line-height: 28px; font-weight: 700; color: #111827;">
                            {{ $siteName }}
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding: 8px 24px 24px 24px; font-size: 16px; line-height: 24px; color: #1f2937;">
                            {{ $slot }}
                        </td>
                    </tr>
                    @if (!empty($legalText))
                        <tr>
                            <td
                                style="padding: 16px 24px 24px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; line-height: 18px; color: #6b7280;">
                                {!! $legalText !!}
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
