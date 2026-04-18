@props([
    'legend',
    'options' => [],
    'model',
    'emptyMessage' => null,
    'valueKey' => 'value',
    'labelKey' => 'label',
])

@if ($options !== [])
    <div class="mb-2" data-admin-field="multi-checkbox-pills">
        <fieldset>
            <legend class="text-sm font-semibold text-stone-800">
                {{ $legend }}
            </legend>

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($options as $option)
                    @php
                        $value = $option[$valueKey] ?? null;
                        $label = $option[$labelKey] ?? null;
                    @endphp

                    @if ($value !== null && $label !== null)
                        <label class="cursor-pointer select-none"
                            data-admin-pill-option="{{ $value }}">
                            <input type="checkbox" wire:model.live="{{ $model }}"
                                value="{{ $value }}" class="sr-only peer" />
                            <span
                                class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors peer-checked:border-[#d9755b] peer-checked:bg-[#d9755b] peer-checked:text-white peer-checked:hover:bg-[#793d3d] peer-checked:hover:border-[#793d3d] peer-checked:hover:text-white border-gray-300 text-gray-600 hover:border-[#d9755b]/50 hover:bg-[#edd2c7]/20">
                                {{ $label }}
                            </span>
                        </label>
                    @endif
                @endforeach
            </div>
        </fieldset>
    </div>
@elseif ($emptyMessage)
    <p class="rounded-md border border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-600">
        {{ $emptyMessage }}
    </p>
@endif
