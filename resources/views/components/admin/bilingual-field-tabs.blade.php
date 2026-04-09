@props([
    'title',
    'localeConfigs' => [],
    'type' => 'text',
    'rows' => 4,
])

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
        <div x-cloak x-show="tab === '{{ $tabConfig['key'] }}'"
            x-bind:hidden="tab !== '{{ $tabConfig['key'] }}'"
            data-bilingual-pane="{{ $tabConfig['key'] }}"
            class="space-y-2">
            <label for="{{ $tabConfig['field'] }}" class="block text-sm font-medium text-gray-700">
                {{ $tabConfig['fieldLabel'] }}
                @if ($loop->first)
                    <span class="text-red-500">*</span>
                @endif
            </label>

            @if ($type === 'textarea')
                <textarea id="{{ $tabConfig['field'] }}" wire:model="{{ $tabConfig['field'] }}" rows="{{ $rows }}"
                    @class([
                        'block w-full rounded-md shadow-sm focus:border-[#d9755b] focus:ring-[#d9755b] sm:text-sm',
                        'border-red-500' => $errors->has($tabConfig['field']),
                        'border-gray-300' => !$errors->has($tabConfig['field']),
                    ])></textarea>
            @else
                <input id="{{ $tabConfig['field'] }}" type="{{ $type }}" wire:model="{{ $tabConfig['field'] }}"
                    @class([
                        'block w-full rounded-md shadow-sm focus:border-[#d9755b] focus:ring-[#d9755b] sm:text-sm',
                        'border-red-500' => $errors->has($tabConfig['field']),
                        'border-gray-300' => !$errors->has($tabConfig['field']),
                    ]) />
            @endif

            @error($tabConfig['field'])
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>
