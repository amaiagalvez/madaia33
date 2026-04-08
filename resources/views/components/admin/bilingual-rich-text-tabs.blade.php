@props(['title', 'localeConfigs' => []])

@php
    $tabs = collect(\App\SupportedLocales::all())
        ->map(function (string $locale) use ($localeConfigs): ?array {
            $localeConfig = $localeConfigs[$locale] ?? null;

            if ($localeConfig === null) {
                return null;
            }

            return [
                'key' => $locale,
                'field' => $localeConfig['field'],
                'fieldLabel' => $localeConfig['fieldLabel'],
                'value' => $localeConfig['value'],
                'tabLabel' => __(\App\SupportedLocales::adminTabTranslationKey($locale)),
            ];
        })
        ->filter()
        ->values()
        ->all();

    $initialTab = $tabs[0]['key'] ?? \App\SupportedLocales::default();
    $rootField = $tabs[0]['field'] ?? '';

    $toolbarActions = [
        [
            'type' => 'format',
            'value' => 'bold',
            'label' => __('admin.settings_form.editor_bold'),
        ],
        [
            'type' => 'format',
            'value' => 'italic',
            'label' => __('admin.settings_form.editor_italic'),
        ],
        [
            'type' => 'format',
            'value' => 'underline',
            'label' => __('admin.settings_form.editor_underline'),
        ],
        [
            'type' => 'link',
            'label' => __('admin.settings_form.editor_link'),
        ],
    ];
@endphp

<div x-data="{ tab: '{{ $initialTab }}' }" class="space-y-6 rounded-lg border border-gray-200 bg-stone-50 p-4"
    data-bilingual-field="{{ $rootField }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm font-semibold text-stone-800">{{ $title }}</p>
        <nav class="flex w-fit gap-1 rounded-md border border-gray-200 bg-white p-1"
            aria-label="{{ __('admin.settings_form.language_tabs') }}">
            @foreach ($tabs as $tabConfig)
                <button type="button" @click="tab = '{{ $tabConfig['key'] }}'"
                    data-bilingual-tab="{{ $tabConfig['key'] }}"
                    :class="tab === '{{ $tabConfig['key'] }}' ? 'bg-[#edd2c7] text-[#793d3d]' :
                        'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]'"
                    class="rounded-md px-3 py-1.5 text-xs font-semibold transition-colors"
                    :aria-selected="tab === '{{ $tabConfig['key'] }}' ? 'true' : 'false'">
                    {{ $tabConfig['tabLabel'] }}
                </button>
            @endforeach
        </nav>
    </div>

    @foreach ($tabs as $tabConfig)
        <template x-if="tab === '{{ $tabConfig['key'] }}'">
            <div x-cloak data-bilingual-pane="{{ $tabConfig['key'] }}"
                class="grid h-64 grid-rows-[1fr_auto] gap-1 overflow-hidden">
                <p id="{{ $tabConfig['field'] }}Label" class="sr-only">
                    {{ $tabConfig['fieldLabel'] }}</p>
                <div class="rounded-md border border-gray-300 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-2"
                        role="toolbar" aria-label="{{ __('admin.settings_form.editor_toolbar') }}">
                        @foreach ($toolbarActions as $toolbarAction)
                            <button type="button"
                                @click="{{ $toolbarAction['type'] === 'link' ? "link('{$tabConfig['field']}')" : "format('{$tabConfig['field']}', '{$toolbarAction['value']}')" }}"
                                class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-[#edd2c7]/45 hover:text-[#793d3d]">{{ $toolbarAction['label'] }}</button>
                        @endforeach
                    </div>
                    <div id="{{ $tabConfig['field'] }}" x-ref="{{ $tabConfig['field'] }}"
                        contenteditable="true" role="textbox"
                        aria-labelledby="{{ $tabConfig['field'] }}Label" aria-multiline="true"
                        @input="sync('{{ $tabConfig['field'] }}')"
                        class="h-52 w-full overflow-y-auto bg-white px-3 py-2 text-sm text-stone-900 focus:outline-none">
                        {!! $tabConfig['value'] !!}</div>
                </div>
                <div class="min-h-5">
                    @isset($errors)
                        @error($tabConfig['field'])
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @endisset
                </div>
            </div>
        </template>
    @endforeach
</div>
