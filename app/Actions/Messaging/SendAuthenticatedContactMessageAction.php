<?php

namespace App\Actions\Messaging;

use App\Models\User;
use App\Models\Owner;
use App\Models\Setting;
use App\Models\ContactMessage;
use App\Support\ContactMailData;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;
use App\Support\ContactConfirmationSubject;
use App\Support\Messaging\CampaignTrackingUrlBuilder;
use App\Actions\Campaigns\RecordDirectMessageRecipientAction;

class SendAuthenticatedContactMessageAction
{
    private const EMAIL_SETTING_KEYS = [
        'admin_email',
        'from_address',
        'from_name',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
    ];

    public function execute(
        User $user,
        ContactMessage $contactMessage,
        string $messageBody,
        string $userMailSubject,
        ?string $adminMailSubject = null,
    ): bool {
        try {
            $settings = array_replace(array_fill_keys(self::EMAIL_SETTING_KEYS, ''), Setting::stringValues(self::EMAIL_SETTING_KEYS));

            app(ConfiguredMailSettings::class)->apply($settings);

            $adminEmail = $settings['admin_email'] ?: (string) config('mail.from.address');
            $fromAddress = $settings['from_address'] ?: (string) config('mail.from.address');
            $fromName = $settings['from_name'] !== '' ? $settings['from_name'] : (string) config('mail.from.name');
            $trackingPixelUrl = null;

            if ($user->owner instanceof Owner) {
                $recipient = app(RecordDirectMessageRecipientAction::class)->execute(
                    owner: $user->owner,
                    contact: (string) $user->email,
                    subject: ContactConfirmationSubject::forAudit($userMailSubject),
                    body: $messageBody,
                    sentByUserId: $user->id,
                );

                $trackingPixelUrl = app(CampaignTrackingUrlBuilder::class)->openPixelUrl($recipient->tracking_token);
            }

            Mail::to($user->email)->send(new ContactConfirmation(
                new ContactMailData(
                    visitorName: $user->name,
                    messageSubject: $userMailSubject,
                    messageBody: $messageBody,
                ),
                $fromAddress,
                $fromName,
                $trackingPixelUrl,
            ));

            $adminContactMessage = $contactMessage->replicate();
            $adminContactMessage->subject = $adminMailSubject ?? $userMailSubject;

            Mail::to($adminEmail)->send(new ContactNotification($adminContactMessage, null, $fromAddress, $fromName));

            return false;
        } catch (\Throwable $e) {
            Log::error('SendAuthenticatedContactMessageAction: email send failed', [
                'message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }
}
