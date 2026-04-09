@props(['title', 'localeConfigs' => [], 'type' => 'text', 'rows' => 4])

<x-admin.bilingual-tabs :title="$title" :locale-configs="$localeConfigs" mode="field" :type="$type"
    :rows="$rows" :required-primary="true" />
