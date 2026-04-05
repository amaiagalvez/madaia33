<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use App\Models\ContactMessage;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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

    protected function rules(): array
    {
        $siteKey = (string) (Setting::where('key', 'recaptcha_site_key')->value('value') ?? '');

        return ContactFormValidation::rules($siteKey);
    }

    protected function messages(): array
    {
        return ContactFormValidation::messages();
    }

    public function submit(): void
    {
        $this->validate();

        if (! $this->verifyRecaptcha()) {
            $this->statusType = 'error';
            $this->statusMessage = __('contact.spam_error');
            $this->dispatch('contact-form-submitted');

            return;
        }

        if ($this->hasRecentDuplicateSubmission()) {
            $this->reset(['name', 'email', 'subject', 'message', 'legalAccepted', 'recaptchaToken']);
            $this->statusType = 'success';
            $this->statusMessage = __('contact.success');
            $this->dispatch('contact-form-submitted');

            return;
        }

        $this->rememberRecentSubmission();

        $contactMessage = ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        $emailFailed = false;

        try {
            $adminEmail = Setting::where('key', 'admin_email')->value('value') ?? config('mail.from.address');

            Mail::to($this->email)->send(new ContactConfirmation(
                visitorName: $this->name,
                messageSubject: $this->subject,
                messageBody: $this->message,
            ));

            Mail::to($adminEmail)->send(new ContactNotification(
                visitorName: $this->name,
                visitorEmail: $this->email,
                messageSubject: $this->subject,
                messageBody: $this->message,
            ));
        } catch (\Throwable $e) {
            Log::error('ContactForm: email send failed', [
                'message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
            $emailFailed = true;
        }

        $this->reset(['name', 'email', 'subject', 'message', 'legalAccepted', 'recaptchaToken']);

        if ($emailFailed) {
            $this->statusType = 'warning';
            $this->statusMessage = __('contact.email_error');
        } else {
            $this->statusType = 'success';
            $this->statusMessage = __('contact.success');
        }

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

        return collect(session('contact_form_recent_submissions', []))
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

        $secretKey = Setting::where('key', 'recaptcha_secret_key')->value('value') ?? '';

        if (empty($secretKey)) {
            return true;
        }

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
        $locale = app()->getLocale();
        $legalTextKey = "legal_checkbox_text_{$locale}";
        $settings = Setting::whereIn('key', [$legalTextKey, 'legal_url', 'recaptcha_site_key'])
            ->get(['key', 'value'])
            ->pluck('value', 'key');
        $legalText = (string) ($settings[$legalTextKey] ?? __('contact.legal_text'));
        $legalUrl = (string) ($settings['legal_url'] ?? route('privacy-policy'));
        $siteKey = (string) ($settings['recaptcha_site_key'] ?? '');

        return view('livewire.contact-form', [
            'legalText' => $legalText,
            'legalUrl' => $legalUrl,
            'siteKey' => $siteKey,
        ]);
    }
}
