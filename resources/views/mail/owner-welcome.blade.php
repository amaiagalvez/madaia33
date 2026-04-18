<x-mail.layout :legal-text="$legalText" :tracking-pixel-url="$trackingPixelUrl">
    <div style="margin: 0 0 16px 0;">
        {!! $bodyHtml !!}
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0;">
        <tr>
            <td align="center" style="border-radius: 6px; background-color: #d9755b;">
                <a href="{{ $trackedResetUrl ?? $resetUrl }}"
                    style="display: inline-block; padding: 12px 20px; color: #ffffff; text-decoration: none; font-weight: 600;">
                    {{ __('admin.owners.email.reset_action') }}
                </a>
            </td>
        </tr>
    </table>
</x-mail.layout>
