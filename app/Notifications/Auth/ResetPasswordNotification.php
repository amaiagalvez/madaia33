<?php

namespace App\Notifications\Auth;

use App\Support\EmailLegalText;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(Lang::get('Reset your password'))
            ->action(Lang::get('Reset Password'), $url)
            ->view('mail.auth.reset-password', [
                'intro' => Lang::get('You are receiving this email because we received a password reset request for your account.'),
                'actionText' => Lang::get('Reset Password'),
                'actionUrl' => $url,
                'expiryLine' => Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]),
                'outro' => Lang::get('If you did not request a password reset, no further action is required.'),
                'legalText' => EmailLegalText::resolve(),
            ]);
    }
}
