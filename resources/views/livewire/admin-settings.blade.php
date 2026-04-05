<div>
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
            <textarea id="legalCheckboxTextEu" wire:model="legalCheckboxTextEu" rows="3"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
            @error('legalCheckboxTextEu')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Legal checkbox text ES --}}
        <div>
            <label for="legalCheckboxTextEs" class="block text-sm font-medium text-gray-700">
                {{ __('admin.settings_form.legal_text_es') }}
            </label>
            <textarea id="legalCheckboxTextEs" wire:model="legalCheckboxTextEs" rows="3"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
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
