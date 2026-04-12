@props(['legend', 'options' => [], 'model', 'valueKey' => 'value', 'labelKey' => 'label'])

<div class="mb-2" data-admin-field="single-radio-pills" {{ $attributes }}>
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
                        <input type="radio" wire:model="{{ $model }}"
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

    @error($model)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
