<div class="mb-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" wire:model="ownersSendWelcomeMail"
            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600"
            data-setting="owners-send-welcome-mail">
        <span class="font-medium">{{ __('admin.settings_form.owners_send_welcome_mail') }}</span>
    </label>
</div>

<x-admin.bilingual-tabs :title="__('admin.settings_form.owners_welcome_subject')" :locale-configs="$this->localeConfigsFor(
    'ownersWelcomeSubject',
    'admin.settings_form.owners_welcome_subject',
)" mode="text" type="text"
    rows="1" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.owners_welcome_text')" :locale-configs="$this->localeConfigsFor('ownersWelcomeText', 'admin.settings_form.owners_welcome_text')" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.owners_terms_text')" :locale-configs="$this->localeConfigsFor('ownersTermsText', 'admin.settings_form.owners_terms_text')" />
