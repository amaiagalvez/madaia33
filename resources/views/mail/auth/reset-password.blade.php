<x-mail.layout :legal-text="$legalText">
    <p style="margin: 0 0 16px 0;">{{ $intro }}</p>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
        style="margin: 0 0 16px 0;">
        <tr>
            <td align="center" style="border-radius: 6px; background-color: #1f2937;">
                <a href="{{ $actionUrl }}"
                    style="display: inline-block; padding: 12px 20px; color: #ffffff; text-decoration: none; font-weight: 600;">
                    {{ $actionText }}
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 12px 0;">{{ $expiryLine }}</p>
    <p style="margin: 0;">{{ $outro }}</p>
</x-mail.layout>
