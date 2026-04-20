<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use App\Rules\NoScriptTags;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Actions\Messaging\SendAuthenticatedContactMessageAction;

class ProfileContactModal extends Component
{
    private const EMAIL_SETTING_KEYS = [
        'contact_form_subject_eu',
        'contact_form_subject_es',
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
            'subject' => $userMailSubject,
            'message' => $this->message,
        ]);

        $emailFailed = app(SendAuthenticatedContactMessageAction::class)->execute(
            user: $user,
            contactMessage: $contactMessage,
            messageBody: $this->message,
            userMailSubject: $userMailSubject,
            adminMailSubject: $adminMailSubject,
        );

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
     * @return array<string, string>
     */
    private function settings(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        $this->cachedSettings = Setting::stringValues(self::EMAIL_SETTING_KEYS);

        return $this->cachedSettings;
    }

    public function render(): View
    {
        return view('livewire.front.profile-contact-modal');
    }
}
