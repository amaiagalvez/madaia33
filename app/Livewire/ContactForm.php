<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use App\SupportedLocales;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;
use App\Validations\ContactFormValidation;

class ContactForm extends Component
{
    private const DUPLICATE_SUBMISSION_WINDOW_SECONDS = 15;

    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $legalAccepted = false;

    public string $recaptchaToken = '';

    /** @var 'success'|'warning'|'error'|null */
    public ?string $statusType = null;

    public string $statusMessage = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $siteKey = (string) (Setting::where('key', 'recaptcha_site_key')->value('value') ?? '');

        return ContactFormValidation::rules($siteKey);
    }

    /**
     * @return array<string, mixed>
     */
    protected function messages(): array
    {
        return ContactFormValidation::messages();
    }

    public function submit(): void
    {
        $this->validate();

        if (! $this->verifyRecaptcha()) {
            $this->markAsSpamRejected();

            return;
        }

        if ($this->hasRecentDuplicateSubmission()) {
            $this->markAsSuccessfulSubmission();

            return;
        }

        $this->rememberRecentSubmission();
        $contactMessage = $this->storeContactMessage();
        $emailFailed = $this->sendContactEmails($contactMessage);

        $this->markSubmissionAsCompleted($emailFailed);
    }

    private function markAsSpamRejected(): void
    {
        $this->statusType = 'error';
        $this->statusMessage = __('contact.spam_error');
        $this->dispatch('contact-form-submitted');
    }

    private function markAsSuccessfulSubmission(): void
    {
        $this->resetFormFields();
        $this->statusType = 'success';
        $this->statusMessage = __('contact.success');
        $this->dispatch('contact-form-submitted');
    }

    private function resetFormFields(): void
    {
        $this->reset(['name', 'email', 'subject', 'message', 'legalAccepted', 'recaptchaToken']);
    }

    private function storeContactMessage(): ContactMessage
    {
        return ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);
    }

    private function sendContactEmails(ContactMessage $contactMessage): bool
    {
        try {
            $emailSettings = $this->emailSettings();
            $this->configuredMailSettings()->apply($emailSettings);
            $adminEmail = $emailSettings['admin_email'] ?: (string) config('mail.from.address');
            $fromAddress = $emailSettings['from_address'] ?: (string) config('mail.from.address');
            $fromName = ($emailSettings['from_name'] ?? '') !== '' ? $emailSettings['from_name'] : (string) config('mail.from.name');
            $legalText = $this->resolveLocalizedSetting($emailSettings, 'legal_text');

            Mail::to($this->email)->send(new ContactConfirmation(
                visitorName: $this->name,
                messageSubject: $this->subject,
                messageBody: $this->message,
                legalText: $legalText,
                fromAddress: $fromAddress,
                fromName: $fromName,
            ));

            Mail::to($adminEmail)->send(new ContactNotification($contactMessage, $legalText, $fromAddress, $fromName));

            return false;
        } catch (\Throwable $e) {
            Log::error('ContactForm: email send failed', [
                'message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }

    private function markSubmissionAsCompleted(bool $emailFailed): void
    {
        $this->resetFormFields();
        $this->statusType = $emailFailed ? 'warning' : 'success';
        $this->statusMessage = $emailFailed ? __('contact.email_error') : __('contact.success');
        $this->dispatch('contact-form-submitted');
    }

    private function hasRecentDuplicateSubmission(): bool
    {
        return array_key_exists($this->submissionFingerprint(), $this->recentSubmissions());
    }

    private function rememberRecentSubmission(): void
    {
        $submissions = $this->recentSubmissions();
        $submissions[$this->submissionFingerprint()] = now()->getTimestamp();

        session(['contact_form_recent_submissions' => $submissions]);
    }

    /**
     * @return array<string, int>
     */
    private function recentSubmissions(): array
    {
        $threshold = now()->subSeconds(self::DUPLICATE_SUBMISSION_WINDOW_SECONDS)->getTimestamp();
        $storedSubmissions = session('contact_form_recent_submissions', []);

        if (! is_array($storedSubmissions)) {
            return [];
        }

        return collect($storedSubmissions)
            ->filter(fn (mixed $timestamp, mixed $fingerprint): bool => is_string($fingerprint)
                && is_numeric($timestamp)
                && (int) $timestamp >= $threshold)
            ->map(fn (mixed $timestamp): int => (int) $timestamp)
            ->all();
    }

    private function submissionFingerprint(): string
    {
        return hash('sha256', implode('|', [
            session()->getId(),
            trim($this->name),
            mb_strtolower(trim($this->email)),
            trim($this->subject),
            trim($this->message),
        ]));
    }

    private function verifyRecaptcha(): bool
    {
        // Skip verification when secret key is empty (dev/test) or explicitly skipped
        if (config('app.recaptcha_skip', false)) {
            return true;
        }

        $secretKey = $this->recaptchaSecretKey();

        if (empty($secretKey)) {
            return true;
        }

        return $this->verifyRecaptchaToken($secretKey);
    }

    private function recaptchaSecretKey(): string
    {
        return (string) (Setting::where('key', 'recaptcha_secret_key')->value('value') ?? '');
    }

    private function configuredMailSettings(): ConfiguredMailSettings
    {
        return app(ConfiguredMailSettings::class);
    }

    /**
     * @return array<string, string>
     */
    private function emailSettings(): array
    {
        return Setting::whereIn('key', [
            'admin_email',
            'from_address',
            'from_name',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            ...SupportedLocales::localizedKeys('legal_text'),
        ])
            ->pluck('value', 'key')
            ->map(fn (mixed $value): string => (string) $value)
            ->all();
    }

    /**
     * @param  array<string, string>  $settings
     */
    private function resolveLocalizedSetting(array $settings, string $prefix, ?string $fallback = null): ?string
    {
        foreach (SupportedLocales::localizedKeys($prefix) as $key) {
            $value = trim((string) ($settings[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    private function verifyRecaptchaToken(string $secretKey): bool
    {
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $this->recaptchaToken,
            ]);

            $data = $response->json();

            return ($data['success'] ?? false) && ($data['score'] ?? 0) >= 0.5;
        } catch (\Throwable $e) {
            Log::error('ContactForm: reCAPTCHA verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function render(): View
    {
        $settings = Setting::whereIn('key', [
            ...SupportedLocales::localizedKeys('legal_checkbox_text'),
            'recaptcha_site_key',
        ])
            ->pluck('value', 'key')
            ->map(fn (mixed $value): string => (string) $value)
            ->all();

        $legalText = $this->resolveLocalizedSetting($settings, 'legal_checkbox_text', __('contact.legal_text'));

        $siteKey = $settings['recaptcha_site_key'] ?? '';

        return view('livewire.front.contact-form', [
            'legalText' => $legalText,
            'siteKey' => $siteKey,
        ]);
    }
}
