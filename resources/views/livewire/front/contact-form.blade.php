<div x-data="{
    subjectCount: 0,
    messageCount: 0,
    showLegalModal: false,
    focusFirstField() {
        this.$refs.firstField?.focus();
    },
    autoResizeTextarea(el) {
        if (!el) {
            return;
        }

        el.style.height = 'auto';
        el.style.height = `${el.scrollHeight}px`;
    },
    syncCounters() {
        this.subjectCount = this.$refs.subjectField?.value.length ?? 0;
        this.messageCount = this.$refs.messageField?.value.length ?? 0;
        this.autoResizeTextarea(this.$refs.messageField);
    }
}" x-init="$nextTick(() => {
    focusFirstField();
    syncCounters();
})"
    @contact-form-submitted.window="focusFirstField()">
    <x-front.public-page-header hero="contact" :title="__('contact.title')" :subtitle="__('contact.subtitle')">
        </x-public-page-header>

        <div class="hero-frame p-5 sm:p-6">
            <div class="max-w-3xl">
                {{-- Status message --}}
                @if ($statusType)
                    <div role="alert" aria-live="polite"
                        class="mb-6 rounded-2xl border px-4 py-4 sm:px-5"
                        @class([
                            'border-green-300 bg-linear-to-r from-green-50 to-white text-green-900' =>
                                $statusType === 'success',
                            'border-[#f1bd4d]/70 bg-linear-to-r from-[#f1bd4d]/20 to-white text-[#793d3d]' =>
                                $statusType === 'warning',
                            'border-red-300 bg-linear-to-r from-red-50 to-white text-red-900' =>
                                $statusType === 'error',
                        ])>
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                                @class([
                                    'bg-green-100 text-green-700' => $statusType === 'success',
                                    'bg-[#f1bd4d]/40 text-[#793d3d]' => $statusType === 'warning',
                                    'bg-red-100 text-red-700' => $statusType === 'error',
                                ])>
                                @if ($statusType === 'success')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @elseif($statusType === 'warning')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.051 3.378c.866-1.5 3.032-1.5 3.898 0l7.354 12.748ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.051 3.378c.866-1.5 3.032-1.5 3.898 0l7.354 12.748ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                @endif
                            </div>

                            <div>
                                <p class="text-sm font-semibold">{{ $statusMessage }}</p>
                                @if ($statusType === 'success')
                                    <p class="mt-1 text-xs text-green-800/80">
                                        {{ __('contact.success_hint') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit="submit"
                    x-on:submit="$refs.submitButton?.setAttribute('disabled', 'disabled')"
                    novalidate>
                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="contact-name"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('contact.name') }} <span aria-hidden="true"
                                class="text-red-500">*</span>
                        </label>
                        <input id="contact-name" type="text" wire:model="name"
                            autocomplete="name" maxlength="255" x-ref="firstField"
                            class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-[#d9755b] focus:ring-[#d9755b]' }}"
                            aria-describedby="{{ $errors->has('name') ? 'contact-name-error' : '' }}"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                        @error('name')
                            <p id="contact-name-error" class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="contact-email"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('contact.email') }} <span aria-hidden="true"
                                class="text-red-500">*</span>
                        </label>
                        <input id="contact-email" type="email" wire:model="email"
                            autocomplete="email" maxlength="255"
                            class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('email') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-[#d9755b] focus:ring-[#d9755b]' }}"
                            aria-describedby="{{ $errors->has('email') ? 'contact-email-error' : '' }}"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                        @error('email')
                            <p id="contact-email-error" class="text-red-600 text-sm mt-1">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="mb-4">
                        <label for="contact-subject"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('contact.subject') }} <span aria-hidden="true"
                                class="text-red-500">*</span>
                        </label>
                        <input id="contact-subject" type="text" wire:model="subject"
                            maxlength="255" x-ref="subjectField"
                            @input="subjectCount = $event.target.value.length"
                            class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('subject') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-[#d9755b] focus:ring-[#d9755b]' }}"
                            aria-describedby="{{ $errors->has('subject') ? 'contact-subject-error' : '' }}"
                            aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}">
                        <p id="contact-subject-counter" class="mt-1 text-xs text-gray-500"
                            aria-live="polite">
                            {{ __('contact.subject_count') }} <span
                                x-text="subjectCount">0</span>/255
                        </p>
                        @error('subject')
                            <p id="contact-subject-error" class="text-red-600 text-sm mt-1">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div class="mb-4">
                        <label for="contact-message"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('contact.message') }} <span aria-hidden="true"
                                class="text-red-500">*</span>
                        </label>
                        <textarea id="contact-message" wire:model="message" rows="3" maxlength="5000"
                            x-ref="messageField"
                            @input="messageCount = $event.target.value.length; autoResizeTextarea($event.target)"
                            class="block w-full min-h-11 rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1
                    {{ $errors->has('message') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-[#d9755b] focus:ring-[#d9755b]' }}"
                            aria-describedby="{{ $errors->has('message') ? 'contact-message-error' : '' }}"
                            aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"></textarea>
                        <p id="contact-message-counter" class="mt-1 text-xs text-gray-500"
                            aria-live="polite">
                            {{ __('contact.message_count') }} <span
                                x-text="messageCount">0</span>/5000
                        </p>
                        @error('message')
                            <p id="contact-message-error" class="text-red-600 text-sm mt-1">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Legal checkbox --}}
                    <div class="mb-6">
                        <div class="flex items-start gap-2">
                            <label for="contact-legal"
                                class="sr-only">{{ $checkboxLabel }}</label>
                            <input id="contact-legal" type="checkbox" wire:model="legalAccepted"
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]"
                                aria-describedby="{{ $errors->has('legalAccepted') ? 'contact-legal-error' : '' }}"
                                aria-invalid="{{ $errors->has('legalAccepted') ? 'true' : 'false' }}">
                            <div class="text-sm text-gray-700">
                                <button type="button" @click.prevent.stop="showLegalModal = true"
                                    class="cursor-pointer text-left underline decoration-[#d9755b]/40 underline-offset-4 hover:text-[#793d3d]"
                                    data-test="contact-legal-modal-trigger">
                                    {{ $checkboxLabel }}
                                </button>
                                <span aria-hidden="true" class="text-red-500">*</span>
                            </div>
                        </div>
                        @error('legalAccepted')
                            <p id="contact-legal-error" class="text-red-600 text-sm mt-1">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- reCAPTCHA token (hidden, populated by JS) --}}
                    <input type="hidden" wire:model="recaptchaToken" id="recaptcha-token">

                    {{-- Submit --}}
                    <button type="submit" x-ref="submitButton"
                        class="inline-flex w-full sm:w-auto min-h-11 items-center justify-center rounded-md bg-[#d9755b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2 disabled:opacity-50"
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

                <template x-teleport="body">
                    <div x-cloak x-show="showLegalModal" x-transition.opacity
                        x-on:keydown.escape.window="showLegalModal = false"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                        aria-modal="true" role="dialog"
                        aria-label="{{ __('general.footer.privacy_policy') }}">
                        <div class="absolute inset-0 bg-black/50" @click="showLegalModal = false"
                            aria-hidden="true">
                        </div>

                        <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
                            @click.stop data-test="contact-legal-modal">
                            <div
                                class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ __('general.footer.privacy_policy') }}
                                </h2>
                                <button type="button" @click="showLegalModal = false"
                                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                    aria-label="{{ __('general.buttons.close') }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[80vh] overflow-y-auto px-5 py-4 text-sm leading-6 text-gray-700"
                                data-test="contact-legal-modal-content">
                                {!! $legalModalText !!}
                            </div>
                        </div>
                    </div>
                </template>

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
        </div>
</div>
