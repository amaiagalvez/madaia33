<div x-data="{
    subjectCount: 0,
    messageCount: 0,
    focusFirstField() {
        this.$refs.firstField?.focus();
    },
    syncCounters() {
        this.subjectCount = this.$refs.subjectField?.value.length ?? 0;
        this.messageCount = this.$refs.messageField?.value.length ?? 0;
    }
}" x-init="$nextTick(() => {
    focusFirstField();
    syncCounters();
})"
    @contact-form-submitted.window="focusFirstField()">
    {{-- Status message --}}
    @if ($statusType)
        <div role="alert" aria-live="polite"
            class="mb-6 rounded-lg px-4 py-3 text-sm font-medium
            {{ $statusType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : '' }}
            {{ $statusType === 'warning' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
            {{ $statusType === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : '' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="submit" x-on:submit="$refs.submitButton?.setAttribute('disabled', 'disabled')"
        novalidate>
        {{-- Name --}}
        <div class="mb-4">
            <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.name') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input id="contact-name" type="text" wire:model="name" autocomplete="name"
                maxlength="255" x-ref="firstField"
                class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }}"
                aria-describedby="{{ $errors->has('name') ? 'contact-name-error' : '' }}"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
            @error('name')
                <p id="contact-name-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.email') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input id="contact-email" type="email" wire:model="email" autocomplete="email"
                maxlength="255"
                class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('email') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }}"
                aria-describedby="{{ $errors->has('email') ? 'contact-email-error' : '' }}"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
            @error('email')
                <p id="contact-email-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subject --}}
        <div class="mb-4">
            <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.subject') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <input id="contact-subject" type="text" wire:model="subject" maxlength="255"
                x-ref="subjectField" @input="subjectCount = $event.target.value.length"
                class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('subject') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }}"
                aria-describedby="{{ $errors->has('subject') ? 'contact-subject-error' : '' }}"
                aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}">
            <p id="contact-subject-counter" class="mt-1 text-xs text-gray-500" aria-live="polite">
                {{ __('contact.subject_count') }} <span x-text="subjectCount">0</span>/255
            </p>
            @error('subject')
                <p id="contact-subject-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div class="mb-4">
            <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('contact.message') }} <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <textarea id="contact-message" wire:model="message" rows="6" maxlength="5000"
                x-ref="messageField" @input="messageCount = $event.target.value.length"
                class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('message') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }}"
                aria-describedby="{{ $errors->has('message') ? 'contact-message-error' : '' }}"
                aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"></textarea>
            <p id="contact-message-counter" class="mt-1 text-xs text-gray-500" aria-live="polite">
                {{ __('contact.message_count') }} <span x-text="messageCount">0</span>/5000
            </p>
            @error('message')
                <p id="contact-message-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal checkbox --}}
        <div class="mb-6">
            <div class="flex items-start gap-2">
                <input id="contact-legal" type="checkbox" wire:model="legalAccepted"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    aria-describedby="{{ $errors->has('legalAccepted') ? 'contact-legal-error' : '' }}"
                    aria-invalid="{{ $errors->has('legalAccepted') ? 'true' : 'false' }}">
                <label for="contact-legal" class="text-sm text-gray-700">
                    <a href="{{ $legalUrl }}" target="_blank" rel="noopener noreferrer"
                        class="underline decoration-indigo-300 underline-offset-4 hover:text-indigo-700">{{ $legalText }}</a>
                    <span aria-hidden="true" class="text-red-500">*</span>
                </label>
            </div>
            @error('legalAccepted')
                <p id="contact-legal-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- reCAPTCHA token (hidden, populated by JS) --}}
        <input type="hidden" wire:model="recaptchaToken" id="recaptcha-token">

        {{-- Submit --}}
        <button type="submit" x-ref="submitButton"
            class="inline-flex w-full sm:w-auto min-h-11 items-center justify-center rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
            data-contact-submit wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove>{{ __('contact.send') }}</span>
            <span wire:loading wire:target="submit" class="flex items-center gap-2"
                aria-live="polite">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ __('general.sending') }}
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
                        const action = window.innerWidth < 304 ? 'contact_compact' :
                            'contact';
                        grecaptcha.execute('{{ $siteKey }}', {
                            action
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
