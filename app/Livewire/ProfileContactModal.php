<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use App\Rules\NoScriptTags;
use App\Models\ContactMessage;
use App\Support\ContactMailData;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;

class ProfileContactModal extends Component
{
    private const EMAIL_SETTING_KEYS = [
        'admin_email',
        'contact_form_subject_eu',
        'contact_form_subject_es',
        'from_address',
        'from_name',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
    ];

    /** @var array<string, string>|null */
    private ?array $cachedSettings = null;

    public bool $showModal = false;

    public string $message = '';

    /** @var 'success'|'error'|null */
    public ?string $statusType = null;

    public string $statusMessage = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000', new NoScriptTags],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'message.required' => __('profile.contact_modal.validation.message_required'),
            'message.max' => __('profile.contact_modal.validation.message_max'),
        ];
    }

    public function open(): void
    {
        $this->reset(['message', 'statusType', 'statusMessage']);
        $this->showModal = true;
        $this->dispatch('profile-contact-modal-opened');
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->dispatch('profile-contact-modal-closed');
    }

    public function submit(): void
    {
        $this->validate();

        $user = Auth::user();
        $settings = $this->settings();
        $userMailSubject = $this->buildUserSubject($settings);
        $adminMailSubject = $this->buildAdminSubject($userMailSubject);

        $contactMessage = ContactMessage::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'subject' => $adminMailSubject,
            'message' => $this->message,
        ]);

        $emailFailed = $this->sendEmails($contactMessage, $settings, $userMailSubject);

        $this->message = '';

        if ($emailFailed) {
            $this->statusType = 'error';
            $this->statusMessage = __('profile.contact_modal.email_error');
        } else {
            $this->showModal = false;
            $this->dispatch('profile-contact-modal-closed');
            $this->statusType = 'success';
            $this->statusMessage = __('profile.contact_modal.success');
        }
    }

    /**
     * @param  array<string, string>  $settings
     */
    private function buildUserSubject(array $settings): string
    {
        return Setting::localizedStringFrom($settings, 'contact_form_subject') ?? '';
    }

    private function buildAdminSubject(string $baseSubject): string
    {
        return '[' . __('profile.contact_modal.message_subject') . '] ' . $baseSubject;
    }

    /**
     * @param  array<string, string>  $settings
     */
    private function sendEmails(ContactMessage $contactMessage, array $settings, string $mailSubject): bool
    {
        try {
            app(ConfiguredMailSettings::class)->apply($settings);

            $adminEmail = $settings['admin_email'] ?: (string) config('mail.from.address');
            $fromAddress = $settings['from_address'] ?: (string) config('mail.from.address');
            $fromName = ($settings['from_name'] ?? '') !== '' ? $settings['from_name'] : (string) config('mail.from.name');

            Mail::to(Auth::user()->email)->send(new ContactConfirmation(
                new ContactMailData(
                    visitorName: Auth::user()->name,
                    messageSubject: $mailSubject,
                    messageBody: $this->message,
                ),
                $fromAddress,
                $fromName,
            ));

            Mail::to($adminEmail)->send(new ContactNotification($contactMessage, null, $fromAddress, $fromName));

            return false;
        } catch (\Throwable $e) {
            Log::error('ProfileContactModal: email send failed', [
                'message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }

    /**
     * @return array<string, string>
     */
    private function settings(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        $this->cachedSettings = Setting::stringValues(array_values(array_unique(self::EMAIL_SETTING_KEYS)));

        return $this->cachedSettings;
    }

    public function render(): View
    {
        return view('livewire.front.profile-contact-modal');
    }
}
