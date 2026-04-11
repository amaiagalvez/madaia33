<x-admin.bilingual-tabs :title="__('admin.settings_form.owners_welcome_subject')" :locale-configs="$this->localeConfigsFor(
    'ownersWelcomeSubject',
    'admin.settings_form.owners_welcome_subject',
)" mode="text" type="text"
    rows="1" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.owners_welcome_text')" :locale-configs="$this->localeConfigsFor('ownersWelcomeText', 'admin.settings_form.owners_welcome_text')" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.owners_terms_text')" :locale-configs="$this->localeConfigsFor('ownersTermsText', 'admin.settings_form.owners_terms_text')" />
