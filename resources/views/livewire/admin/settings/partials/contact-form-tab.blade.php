{{-- Admin email --}}
<x-admin.form-input name="adminEmail" type="email" :label="__('admin.settings_form.admin_email')" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.legal_text')" :locale-configs="$this->localeConfigsFor('legalCheckboxText', 'admin.settings_form.legal_text')" />
