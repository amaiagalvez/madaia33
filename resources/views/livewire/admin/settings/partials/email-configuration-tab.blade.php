<div class="grid gap-6 md:grid-cols-2">
    <x-admin.form-input name="emailFromAddress" type="email" :label="__('admin.settings_form.email_from_address')" />

    <x-admin.form-input name="emailFromName" :label="__('admin.settings_form.email_from_name')" />

    <x-admin.form-input name="smtpHost" :label="__('admin.settings_form.smtp_host')" />

    <x-admin.form-input name="smtpPort" type="number" :min="1" :max="65535"
        :label="__('admin.settings_form.smtp_port')" />

    <x-admin.form-input name="smtpUsername" :label="__('admin.settings_form.smtp_username')" />

    <x-admin.form-input name="smtpPassword" type="password" :label="__('admin.settings_form.smtp_password')" />

    <div>
        <label for="smtpEncryption" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.smtp_encryption') }}
        </label>
        <select id="smtpEncryption" wire:model="smtpEncryption"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
            <option value="">{{ __('admin.settings_form.smtp_encryption_none') }}</option>
            <option value="tls">STARTTLS / TLS (587)</option>
            <option value="ssl">SSL / SMTPS (465)</option>
        </select>
        @error('smtpEncryption')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <flux:button wire:click="openTestEmailModal" variant="primary" icon="paper-airplane"
        :disabled="$hasUnsavedChanges" wire:loading.attr="disabled" wire:target="save">
        {{ __('admin.test_email.button') }}
    </flux:button>
    @if ($hasUnsavedChanges)
        <p class="mt-2 text-xs text-stone-500">
            {{ __('admin.settings_form.save_before_test_email') }}</p>
    @endif
</div>

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.email_legal_text')" :locale-configs="$this->localeConfigsFor('emailLegalText', 'admin.settings_form.email_legal_text')" />
