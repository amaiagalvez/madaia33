<div x-data="{
    format(field, command) {
        const editor = this.$refs[field];
        if (! editor) {
            return;
        }

        editor.focus();
        document.execCommand(command, false, null);
        this.sync(field);
    },
    link(field) {
        const editor = this.$refs[field];
        if (! editor) {
            return;
        }

        const url = window.prompt('{{ __('admin.settings_form.editor_link_prompt') }}', 'https://');
        if (! url) {
            return;
        }

        editor.focus();
        document.execCommand('createLink', false, url);
        this.sync(field);
    },
    sync(field) {
        const editor = this.$refs[field];
        if (! editor) {
            return;
        }

        const html = editor.innerHTML.trim();
        this.$wire.set(field, html === '<br>' ? '' : html);
    },
}"
>
    @if ($saved)
        <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ __('general.messages.saved') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Admin email --}}
        <div>
            <label for="adminEmail" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.admin_email') }}
            </label>
            <input id="adminEmail" type="email" wire:model="adminEmail"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            @error('adminEmail')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- reCAPTCHA site key --}}
        <div>
            <label for="recaptchaSiteKey" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.recaptcha_site_key') }}
            </label>
            <input id="recaptchaSiteKey" type="text" wire:model="recaptchaSiteKey"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            @error('recaptchaSiteKey')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- reCAPTCHA secret key (password field) --}}
        <div>
            <label for="recaptchaSecretKey" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.recaptcha_secret_key') }}
            </label>
            <input id="recaptchaSecretKey" type="password" wire:model="recaptchaSecretKey"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            @error('recaptchaSecretKey')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal checkbox text EU --}}
        <div>
            <label for="legalCheckboxTextEu" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.legal_text_eu') }}
            </label>
            <div class="mt-1 rounded-md border border-gray-300">
                <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-2" role="toolbar"
                    aria-label="{{ __('admin.settings_form.editor_toolbar') }}">
                    <button type="button" @click="format('legalCheckboxTextEu', 'bold')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_bold') }}</button>
                    <button type="button" @click="format('legalCheckboxTextEu', 'italic')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_italic') }}</button>
                    <button type="button" @click="format('legalCheckboxTextEu', 'underline')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_underline') }}</button>
                    <button type="button" @click="link('legalCheckboxTextEu')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_link') }}</button>
                </div>
                <div id="legalCheckboxTextEu" x-ref="legalCheckboxTextEu" contenteditable="true" role="textbox"
                    aria-multiline="true" @input="sync('legalCheckboxTextEu')"
                    class="min-h-24 w-full px-3 py-2 text-sm text-gray-900 focus:outline-none">{!! $legalCheckboxTextEu !!}</div>
            </div>
            @error('legalCheckboxTextEu')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal checkbox text ES --}}
        <div>
            <label for="legalCheckboxTextEs" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.legal_text_es') }}
            </label>
            <div class="mt-1 rounded-md border border-gray-300">
                <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-2" role="toolbar"
                    aria-label="{{ __('admin.settings_form.editor_toolbar') }}">
                    <button type="button" @click="format('legalCheckboxTextEs', 'bold')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_bold') }}</button>
                    <button type="button" @click="format('legalCheckboxTextEs', 'italic')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_italic') }}</button>
                    <button type="button" @click="format('legalCheckboxTextEs', 'underline')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_underline') }}</button>
                    <button type="button" @click="link('legalCheckboxTextEs')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('admin.settings_form.editor_link') }}</button>
                </div>
                <div id="legalCheckboxTextEs" x-ref="legalCheckboxTextEs" contenteditable="true" role="textbox"
                    aria-multiline="true" @input="sync('legalCheckboxTextEs')"
                    class="min-h-24 w-full px-3 py-2 text-sm text-gray-900 focus:outline-none">{!! $legalCheckboxTextEs !!}</div>
            </div>
            @error('legalCheckboxTextEs')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal URL --}}
        <div>
            <label for="legalUrl" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.legal_url') }}
            </label>
            <input id="legalUrl" type="url" wire:model="legalUrl"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            @error('legalUrl')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('general.buttons.save') }}
            </button>
        </div>
    </form>
</div>
