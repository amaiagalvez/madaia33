<div class="grid gap-6 md:grid-cols-2">
    <x-admin.form-input name="frontSiteName" :label="__('admin.settings_form.front_site_name')" />

    <x-admin.form-input name="frontPrimaryEmail" type="email" :label="__('admin.settings_form.front_primary_email')" />

    <div class="space-y-4 md:col-span-2">
        <div>
            <label for="frontLogoImage" class="mb-2 block text-sm font-medium text-stone-700">
                {{ __('admin.settings_form.front_logo_image_path') }}
            </label>
            <input id="frontLogoImage" type="file" wire:model="frontLogoImage"
                accept=".jpg,.jpeg,.png,.webp" data-front-logo-upload
                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-[#edd2c7] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[#793d3d] placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
            <p class="mt-2 text-xs text-stone-500">
                {{ __('admin.settings_form.front_logo_image_help') }}</p>
            @error('frontLogoImage')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($this->frontLogoPreviewUrl)
            <div data-front-logo-preview class="rounded-xl border border-gray-200 bg-stone-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                    {{ __('admin.settings_form.current_front_logo') }}
                </p>
                <img src="{{ $this->frontLogoPreviewUrl }}"
                    alt="{{ __('admin.settings_form.front_logo_image_path') }}"
                    class="mt-3 h-20 w-auto max-w-full rounded-lg border border-gray-200 bg-white object-contain p-2" />
                @if ($frontLogoImagePath !== '')
                    <p class="mt-3 break-all text-xs text-stone-500">{{ $frontLogoImagePath }}</p>
                @endif
            </div>
        @endif
    </div>
</div>

<x-admin.bilingual-field-tabs :title="__('admin.settings_form.front_photo_request_text')" :locale-configs="$this->localeConfigsFor(
    'frontPhotoRequestText',
    'admin.settings_form.front_photo_request_text',
)" mode="plain" type="textarea"
    :rows="3" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.home_history_text')" :locale-configs="$this->localeConfigsFor('historyText', 'admin.settings_form.home_history_text')" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.privacy_policy')" :locale-configs="$this->localeConfigsFor('privacyContent', 'admin.settings_form.privacy_policy')" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.legal_notice')" :locale-configs="$this->localeConfigsFor('legalNoticeContent', 'admin.settings_form.legal_notice')" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.cookie_policy')" :locale-configs="$this->localeConfigsFor('cookiePolicyContent', 'admin.settings_form.cookie_policy')" />
