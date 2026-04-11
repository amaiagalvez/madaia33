<div class="grid gap-6 md:grid-cols-2">
    <x-admin.form-input name="frontSiteName" :label="__('admin.settings_form.front_site_name')" />

    <x-admin.form-input name="frontPrimaryEmail" type="email" :label="__('admin.settings_form.front_primary_email')" />

    <div class="md:col-span-2">
        <x-admin.form-input name="frontLogoImagePath" :label="__('admin.settings_form.front_logo_image_path')" />
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
