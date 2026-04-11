{{-- Admin email --}}
<x-admin.form-input name="adminEmail" type="email" :label="__('admin.settings_form.admin_email')" />

<x-admin.bilingual-field-tabs :title="__('admin.settings_form.contact_form_subject')" :locale-configs="$this->localeConfigsFor('contactFormSubject', 'admin.settings_form.contact_form_subject')" mode="plain" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.legal_text')" :locale-configs="$this->localeConfigsFor('legalCheckboxText', 'admin.settings_form.legal_text')" />
