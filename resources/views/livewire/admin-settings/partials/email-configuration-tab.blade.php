<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="emailFromAddress" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.email_from_address') }}
        </label>
        <input id="emailFromAddress" type="email" wire:model="emailFromAddress"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        @error('emailFromAddress')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="emailFromName" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.email_from_name') }}
        </label>
        <input id="emailFromName" type="text" wire:model="emailFromName"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        @error('emailFromName')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="smtpHost" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.smtp_host') }}
        </label>
        <input id="smtpHost" type="text" wire:model="smtpHost"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        @error('smtpHost')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="smtpPort" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.smtp_port') }}
        </label>
        <input id="smtpPort" type="number" min="1" max="65535" wire:model="smtpPort"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        @error('smtpPort')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="smtpUsername" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.smtp_username') }}
        </label>
        <input id="smtpUsername" type="text" wire:model="smtpUsername"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        @error('smtpUsername')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="smtpPassword" class="block text-sm font-medium text-stone-700">
            {{ __('admin.settings_form.smtp_password') }}
        </label>
        <input id="smtpPassword" type="password" wire:model="smtpPassword"
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        @error('smtpPassword')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

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
    <flux:button wire:click="openTestEmailModal" variant="primary" icon="paper-airplane">
        {{ __('admin.test_email.button') }}
    </flux:button>
</div>

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.email_legal_text')" :locale-configs="$this->localeConfigsFor('emailLegalText', 'admin.settings_form.email_legal_text')" />
