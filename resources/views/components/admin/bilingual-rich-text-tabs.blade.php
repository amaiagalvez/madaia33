@props([
    'title',
    'localeConfigs' => [],
    'mode' => 'rich-text',
    'type' => 'text',
    'rows' => 4,
    'requiredPrimary' => false,
])

<x-admin.bilingual-tabs :title="$title" :locale-configs="$localeConfigs" :mode="$mode" :type="$type"
    :rows="$rows" :required-primary="$requiredPrimary" />
