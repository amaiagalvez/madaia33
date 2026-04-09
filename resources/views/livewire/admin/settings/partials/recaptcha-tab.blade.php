{{-- reCAPTCHA site key --}}
<div>
    <label for="recaptchaSiteKey" class="block text-sm font-medium text-stone-700">
        {{ __('admin.settings_form.recaptcha_site_key') }}
    </label>
    <input id="recaptchaSiteKey" type="text" wire:model="recaptchaSiteKey"
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
    @error('recaptchaSiteKey')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- reCAPTCHA secret key (password field) --}}
<div>
    <label for="recaptchaSecretKey" class="block text-sm font-medium text-stone-700">
        {{ __('admin.settings_form.recaptcha_secret_key') }}
    </label>
    <input id="recaptchaSecretKey" type="password" wire:model="recaptchaSecretKey"
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
    @error('recaptchaSecretKey')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
