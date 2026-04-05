<div>
    {{-- Status message --}}
    @if ($statusType)
        <div role="alert"
            class="mb-6 rounded-lg px-4 py-3 text-sm font-medium
            {{ $statusType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : '' }}
            {{ $statusType === 'warning' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
            {{ $statusType === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : '' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="submit" novalidate>
        {{-- Name --}}
        <div class="mb-4">
            <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.name') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input id="contact-name" type="text" wire:model="name" autocomplete="name" maxlength="255"
                class="block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-1
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-gray-500 focus:ring-gray-500' }}"
                aria-describedby="{{ $errors->has('name') ? 'contact-name-error' : '' }}"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
            @error('name')
                <p id="contact-name-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.email') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input id="contact-email" type="email" wire:model="email" autocomplete="email" maxlength="255"
                class="block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-1
                    {{ $errors->has('email') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-gray-500 focus:ring-gray-500' }}"
                aria-describedby="{{ $errors->has('email') ? 'contact-email-error' : '' }}"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
            @error('email')
                <p id="contact-email-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subject --}}
        <div class="mb-4">
            <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.subject') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input id="contact-subject" type="text" wire:model="subject" maxlength="255"
                class="block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-1
                    {{ $errors->has('subject') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-gray-500 focus:ring-gray-500' }}"
                aria-describedby="{{ $errors->has('subject') ? 'contact-subject-error' : '' }}"
                aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}">
            @error('subject')
                <p id="contact-subject-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div class="mb-4">
            <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.message') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <textarea id="contact-message" wire:model="message" rows="6" maxlength="5000"
                class="block w-full rounded-md border px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-1
                    {{ $errors->has('message') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-gray-500 focus:ring-gray-500' }}"
                aria-describedby="{{ $errors->has('message') ? 'contact-message-error' : '' }}"
                aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"></textarea>
            @error('message')
                <p id="contact-message-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal checkbox --}}
        <div class="mb-6">
            <div class="flex items-start gap-2">
                <input id="contact-legal" type="checkbox" wire:model="legalAccepted"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500"
                    aria-describedby="{{ $errors->has('legalAccepted') ? 'contact-legal-error' : '' }}"
                    aria-invalid="{{ $errors->has('legalAccepted') ? 'true' : 'false' }}">
                <label for="contact-legal" class="text-sm text-gray-700">
                    <a href="{{ $legalUrl }}" target="_blank" rel="noopener noreferrer"
                        class="underline hover:text-gray-900">{{ $legalText }}</a>
                    <span aria-hidden="true" class="text-red-500">*</span>
                </label>
            </div>
            @error('legalAccepted')
                <p id="contact-legal-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- reCAPTCHA token (hidden, populated by JS) --}}
        <input type="hidden" wire:model="recaptchaToken" id="recaptcha-token">

        {{-- Submit --}}
        <button type="submit"
            class="inline-flex items-center justify-center rounded-md bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50"
            wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('contact.send') }}</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ __('contact.send') }}
            </span>
        </button>
    </form>

    @if ($siteKey)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}" defer></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                const form = document.querySelector('[wire\\:submit="submit"]');
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ $siteKey }}', {
                            action: 'contact'
                        }).then(function(token) {
                            @this.set('recaptchaToken', token);
                            @this.call('submit');
                        });
                    });
                }, {
                    once: false
                });
            });
        </script>
    @endif
</div>
