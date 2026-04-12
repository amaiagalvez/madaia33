<div x-data="{
    format(field, command) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            editor.focus();
            document.execCommand(command, false, null);
            this.sync(field);
        },
        link(field) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            const url = window.prompt('{{ __('admin.settings_form.editor_link_prompt') }}', 'https://');
            if (!url) {
                return;
            }

            editor.focus();
            document.execCommand('createLink', false, url);
            this.sync(field);
        },
        sync(field) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            const html = editor.innerHTML.trim();
            this.$wire.set(field, html === '<br>' ? '' : html);
        },
}">
    <div x-data="{ dirty: @entangle('hasUnsavedChanges') }">
        {{-- Section tabs --}}
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
            <nav class="flex flex-wrap gap-2" aria-label="{{ __('admin.settings') }}">
                @foreach ($availableSections as $section)
                    <button type="button"
                        @click.prevent="if (!dirty || window.confirm('{{ __('admin.settings_form.unsaved_changes_confirm') }}')) { $wire.setSection('{{ $section }}') }"
                        data-settings-section="{{ $section }}"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $activeSection === $section ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                        aria-current="{{ $activeSection === $section ? 'page' : 'false' }}">
                        {{ __('admin.settings_sections.' . $section) }}
                    </button>
                @endforeach
            </nav>
        </div>

        @if ($saved)
            <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">
                {{ __('general.messages.saved') }}
            </div>
        @endif

        <form wire:submit="save"
            class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @if ($activeSection === 'contact_form')
                @include('livewire.admin.settings.partials.contact-form-tab')
            @elseif ($activeSection === 'email_configuration')
                @include('livewire.admin.settings.partials.email-configuration-tab')
            @elseif ($activeSection === 'front')
                @include('livewire.admin.settings.partials.front-tab')
            @elseif ($activeSection === 'recaptcha')
                @include('livewire.admin.settings.partials.recaptcha-tab')
            @elseif ($activeSection === 'owners')
                @include('livewire.admin.settings.partials.owners-tab')
            @elseif ($activeSection === 'vote_delegate')
                @include('livewire.admin.settings.partials.vote-delegate-tab')
            @elseif ($activeSection === 'votings')
                @include('livewire.admin.settings.partials.votings-tab')
            @endif

            <div>
                <button type="submit"
                    class="rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    {{ __('general.buttons.save') }}
                </button>
            </div>
        </form>

        @if ($showTestEmailModal)
            <dialog open
                class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-black/40 p-4"
                aria-labelledby="test-email-modal-title">
                <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4 space-y-6">
                    <div>
                        <h2 id="test-email-modal-title" class="text-lg font-semibold text-gray-900">
                            {{ __('admin.test_email.modal_title') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('admin.test_email.modal_description') }}
                        </p>
                    </div>

                    @if ($testEmailStatus === 'error')
                        <div
                            class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                            @if (empty($smtpHost))
                                {{ __('admin.test_email.smtp_not_configured') }}
                            @elseif ($testEmailError)
                                {{ __('admin.test_email.send_failed') }}: {{ $testEmailError }}
                            @else
                                {{ __('admin.test_email.send_failed') }}
                            @endif
                        </div>
                    @endif

                    <div>
                        <label for="testEmailAddress"
                            class="block text-sm font-medium text-stone-700 mb-2">
                            {{ __('admin.test_email.modal_email_label') }}
                        </label>
                        <input id="testEmailAddress" type="email" wire:model="testEmailAddress"
                            placeholder="example@domain.com"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                        @error('testEmailAddress')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="closeTestEmailModal"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                            {{ __('admin.test_email.modal_cancel') }}
                        </button>
                        <button type="button" wire:click="sendTestEmail"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white hover:bg-[#c86248] focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                            <span wire:loading.remove
                                wire:target="sendTestEmail">{{ __('admin.test_email.modal_send') }}</span>
                            <span wire:loading
                                wire:target="sendTestEmail">{{ __('admin.test_email.sending') }}</span>
                        </button>
                    </div>
                </div>
            </dialog>
        @endif
    </div>
</div>
