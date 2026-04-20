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
            $mailConfig = $this->buildMailConfig();
            $trackingPixelUrl = $this->resolveTrackingPixelUrl($user, $messageBody, $userMailSubject);
            $contactMailData = new ContactMailData(
                visitorName: $user->name,
                messageSubject: $userMailSubject,
                messageBody: $messageBody,
            );

            $this->sendUserConfirmation(
                user: $user,
                contactMailData: $contactMailData,
                mailConfig: $mailConfig,
                trackingPixelUrl: $trackingPixelUrl,
            );

            $this->sendAdminNotification(
                contactMessage: $contactMessage,
                userMailSubject: $userMailSubject,
                adminMailSubject: $adminMailSubject,
                mailConfig: $mailConfig,
            );

            return false;
        } catch (\Throwable $e) {
            Log::error('SendAuthenticatedContactMessageAction: email send failed', [
                'message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }

    /**
     * @return array{adminEmail: string, fromAddress: string, fromName: string}
     */
    private function buildMailConfig(): array
    {
        $settings = array_replace(array_fill_keys(self::EMAIL_SETTING_KEYS, ''), Setting::stringValues(self::EMAIL_SETTING_KEYS));

        app(ConfiguredMailSettings::class)->apply($settings);

        return [
            'adminEmail' => $settings['admin_email'] ?: (string) config('mail.from.address'),
            'fromAddress' => $settings['from_address'] ?: (string) config('mail.from.address'),
            'fromName' => $settings['from_name'] !== '' ? $settings['from_name'] : (string) config('mail.from.name'),
        ];
    }

    private function resolveTrackingPixelUrl(User $user, string $messageBody, string $userMailSubject): ?string
    {
        if (! $user->owner instanceof Owner) {
            return null;
        }

        $recipient = app(RecordDirectMessageRecipientAction::class)->execute(
            owner: $user->owner,
            contact: (string) $user->email,
            subject: ContactConfirmationSubject::forAudit($userMailSubject),
            body: $messageBody,
            sentByUserId: $user->id,
        );

        return app(CampaignTrackingUrlBuilder::class)->openPixelUrl($recipient->tracking_token);
    }

    /**
     * @param  array{adminEmail: string, fromAddress: string, fromName: string}  $mailConfig
     */
    private function sendUserConfirmation(
        User $user,
        ContactMailData $contactMailData,
        array $mailConfig,
        ?string $trackingPixelUrl,
    ): void {
        Mail::to($user->email)->send(new ContactConfirmation(
            $contactMailData,
            $mailConfig['fromAddress'],
            $mailConfig['fromName'],
            $trackingPixelUrl,
        ));
    }

    /**
     * @param  array{adminEmail: string, fromAddress: string, fromName: string}  $mailConfig
     */
    private function sendAdminNotification(
        ContactMessage $contactMessage,
        string $userMailSubject,
        ?string $adminMailSubject,
        array $mailConfig,
    ): void {
        $adminContactMessage = $contactMessage->replicate();
        $adminContactMessage->subject = $adminMailSubject ?? $userMailSubject;

        Mail::to($mailConfig['adminEmail'])
            ->send(new ContactNotification($adminContactMessage, null, $mailConfig['fromAddress'], $mailConfig['fromName']));
    }
}
