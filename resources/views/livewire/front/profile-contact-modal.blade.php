<div data-test="profile-contact-modal">
    {{-- Trigger button --}}
    <button type="button" wire:click="open" data-test="profile-contact-modal-trigger"
        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-[#d9755b] hover:text-[#793d3d]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
        {{ __('profile.contact_modal.button') }}
    </button>

    {{-- Status message shown outside modal (after success/close) --}}
    @if ($statusType === 'success' && !$showModal)
        <div role="alert" aria-live="polite"
            class="mt-3 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
            data-test="profile-contact-modal-success">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            {{ $statusMessage }}
        </div>
    @endif

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true"
            role="dialog" aria-labelledby="profile-contact-modal-title"
            data-test="profile-contact-modal-dialog">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50" wire:click="close" aria-hidden="true"></div>

            {{-- Panel --}}
            <div
                class="relative z-10 w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">

                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 id="profile-contact-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('profile.contact_modal.title') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('profile.contact_modal.description') }}
                        </p>
                    </div>
                    <button type="button" wire:click="close"
                        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        aria-label="{{ __('general.buttons.close') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Error status --}}
                @if ($statusType === 'error')
                    <div role="alert" aria-live="polite"
                        class="mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.051 3.378c.866-1.5 3.032-1.5 3.898 0l7.354 12.748ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        {{ $statusMessage }}
                    </div>
                @endif

                <form wire:submit="submit" novalidate>
                    <div class="mb-4">
                        <label for="profile-contact-message"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('profile.contact_modal.message_label') }}
                            <span aria-hidden="true" class="text-red-500">*</span>
                        </label>
                        <textarea id="profile-contact-message" wire:model="message" rows="5" maxlength="5000"
                            data-test="profile-contact-modal-message"
                            class="block w-full rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1 {{ $errors->has('message') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-[#d9755b] focus:ring-[#d9755b]' }}"
                            aria-describedby="{{ $errors->has('message') ? 'profile-contact-message-error' : '' }}"
                            aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"></textarea>
                        @error('message')
                            <p id="profile-contact-message-error" class="mt-1 text-sm text-red-600">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" wire:click="close"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#d9755b] hover:text-[#793d3d]">
                            {{ __('general.buttons.cancel') }}
                        </button>
                        <button type="submit" data-test="profile-contact-modal-submit"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg bg-[#d9755b] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#793d3d]">
                            {{ __('general.buttons.send') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
