<?php

use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|max:5000')]
    public string $message = '';

    #[Validate('accepted')]
    public bool $legalAccepted = false;

    #[Validate('required|string')]
    public string $recaptchaToken = '';

    public string $statusMessage = '';
    public string $statusType = ''; // 'success' | 'warning' | 'error'

    /** @var array<string, mixed>|null */
    private ?array $cachedSettings = null;

    /** @return array<string, mixed> */
    private function settings(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        $locale = app()->getLocale();
        $legalTextKey = "legal_checkbox_text_{$locale}";

        $this->cachedSettings = Setting::query()
            ->whereIn('key', [$legalTextKey, 'legal_url', 'recaptcha_site_key', 'admin_email', 'recaptcha_secret_key'])
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->all();

        return $this->cachedSettings;
    }

    public function getLegalTextProperty(): string
    {
        $locale = app()->getLocale();
        $legalTextKey = "legal_checkbox_text_{$locale}";

        return $this->settings()[$legalTextKey] ?? __('contact.legal_text');
    }

    public function getLegalUrlProperty(): string
    {
        return $this->settings()['legal_url'] ?? route('privacy-policy');
    }

    public function getRecaptchaSiteKeyProperty(): string
    {
        return $this->settings()['recaptcha_site_key'] ?? '';
    }

    public function submit(): void
    {
        $this->validate();

        // Verify reCAPTCHA token (skip in tests when env var is set)
        if (!$this->verifyRecaptcha()) {
            $this->statusMessage = __('contact.spam_error');
            $this->statusType = 'error';

            return;
        }

        // Save the message
        $contactMessage = ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        // Send emails
        $emailFailed = false;

        try {
            $adminEmail = $this->settings()['admin_email'] ?? null;

            Mail::to($this->email)->send(new ContactConfirmation(visitorName: $this->name, messageSubject: $this->subject, messageBody: $this->message));

            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactNotification($contactMessage));
            }
        } catch (\Throwable $e) {
            Log::error('ContactForm: email send failed', [
                'message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
            $emailFailed = true;
        }

        // Reset fields
        $this->name = '';
        $this->email = '';
        $this->subject = '';
        $this->message = '';
        $this->legalAccepted = false;
        $this->recaptchaToken = '';

        $this->statusMessage = $emailFailed ? __('contact.email_error') : __('contact.success');

        $this->statusType = $emailFailed ? 'warning' : 'success';
    }

    private function verifyRecaptcha(): bool
    {
        // Allow bypassing via config (set in phpunit.xml or per-test)
        if (config('app.recaptcha_skip', false)) {
            return true;
        }

        $secretKey = $this->settings()['recaptcha_secret_key'] ?? null;

        if (!$secretKey) {
            return false;
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
};
?>

<div>
    {{-- Status message --}}
    @if ($statusMessage)
        <div class="mb-6 rounded-lg px-4 py-3 text-sm font-medium
            {{ $statusType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : '' }}
            {{ $statusType === 'warning' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
            {{ $statusType === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : '' }}"
            role="alert">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="submit" novalidate>
        {{-- Name --}}
        <div class="mb-4">
            <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.name') }} <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <input id="contact-name" type="text" wire:model="name" autocomplete="name"
                @class([
                    'block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500',
                    'border-red-500' => $errors->has('name'),
                    'border-gray-300' => ! $errors->has('name'),
                ])
                aria-describedby="@error('name') contact-name-error @enderror" aria-required="true">
            @error('name')
                <p id="contact-name-error" class="mt-1 text-xs text-red-600" role="alert">
                    {{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.email') }} <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <input id="contact-email" type="email" wire:model="email" autocomplete="email"
                @class([
                    'block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500',
                    'border-red-500' => $errors->has('email'),
                    'border-gray-300' => ! $errors->has('email'),
                ])
                aria-describedby="@error('email') contact-email-error @enderror"
                aria-required="true">
            @error('email')
                <p id="contact-email-error" class="mt-1 text-xs text-red-600" role="alert">
                    {{ $message }}</p>
            @enderror
        </div>

        {{-- Subject --}}
        <div class="mb-4">
            <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.subject') }} <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <input id="contact-subject" type="text" wire:model="subject"
                @class([
                    'block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500',
                    'border-red-500' => $errors->has('subject'),
                    'border-gray-300' => ! $errors->has('subject'),
                ])
                aria-describedby="@error('subject') contact-subject-error @enderror"
                aria-required="true">
            @error('subject')
                <p id="contact-subject-error" class="mt-1 text-xs text-red-600" role="alert">
                    {{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div class="mb-4">
            <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.message') }} <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <textarea id="contact-message" wire:model="message" rows="6"
                @class([
                    'block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500',
                    'border-red-500' => $errors->has('message'),
                    'border-gray-300' => ! $errors->has('message'),
                ])
                aria-describedby="@error('message') contact-message-error @enderror" aria-required="true"
                maxlength="5000"></textarea>
            @error('message')
                <p id="contact-message-error" class="mt-1 text-xs text-red-600" role="alert">
                    {{ $message }}</p>
            @enderror
        </div>

        {{-- Legal checkbox --}}
        <div class="mb-6">
            <div class="flex items-start gap-3">
                <input id="contact-legal" type="checkbox" wire:model="legalAccepted"
                    @class([
                        'mt-0.5 h-4 w-4 rounded text-gray-800 focus:ring-gray-500',
                        'border-red-500' => $errors->has('legalAccepted'),
                        'border-gray-300' => ! $errors->has('legalAccepted'),
                    ])
                    aria-describedby="@error('legalAccepted') contact-legal-error @enderror"
                    aria-required="true">
                <label for="contact-legal" class="text-sm text-gray-700">
                    {!! $this->legalText !!}
                    <a href="{{ $this->legalUrl }}" target="_blank" rel="noopener noreferrer"
                        class="underline hover:text-gray-900">{{ __('general.privacy_policy') }}</a>
                </label>
            </div>
            @error('legalAccepted')
                <p id="contact-legal-error" class="mt-1 text-xs text-red-600" role="alert">
                    {{ $message }}</p>
            @enderror
        </div>

        {{-- reCAPTCHA token (hidden, populated by JS) --}}
        <input type="hidden" wire:model="recaptchaToken" id="recaptcha-token">

        {{-- Submit --}}
        <button type="submit"
            class="inline-flex items-center justify-center rounded-md bg-gray-800 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50"
            wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('contact.send') }}</span>
            <span wire:loading>{{ __('general.sending') }}</span>
        </button>
    </form>

    @if ($this->recaptchaSiteKey)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $this->recaptchaSiteKey }}" defer>
        </script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                const siteKey = '{{ $this->recaptchaSiteKey }}';

                document.querySelector('form').addEventListener('submit', function(e) {
                    // Token is set by Livewire before submit; ensure it's populated
                    if (!document.getElementById('recaptcha-token').value &&
                        typeof grecaptcha !==
                        'undefined') {
                        e.preventDefault();
                        grecaptcha.ready(function() {
                            grecaptcha.execute(siteKey, {
                                action: 'contact'
                            }).then(function(token) {
                                @this.set('recaptchaToken', token);
                                @this.call('submit');
                            });
                        });
                    }
                });
            });
        </script>
    @endif
</div>
