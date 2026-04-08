<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.privacy_policy')" eu-field="privacyContentEu"
    es-field="privacyContentEs" :eu-label="__('admin.settings_form.privacy_policy_eu')" :es-label="__('admin.settings_form.privacy_policy_es')" :eu-value="$privacyContentEu"
    :es-value="$privacyContentEs" />

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.legal_notice')" eu-field="legalNoticeContentEu"
    es-field="legalNoticeContentEs" :eu-label="__('admin.settings_form.legal_notice_eu')" :es-label="__('admin.settings_form.legal_notice_es')" :eu-value="$legalNoticeContentEu"
    :es-value="$legalNoticeContentEs" />
