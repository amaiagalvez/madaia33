@props([
    'label',
    'model',
    'id' => null,
    'name' => null,
    'containerClass' => null,
    'labelClass' => null,
    'inputClass' => null,
])

@php
    $fieldId = $id ?? str_replace(['.', '[', ']'], '-', $model);
    $errorName = $name ?? $model;
@endphp

<div @class([$containerClass]) data-admin-date-input>
    <label for="{{ $fieldId }}" @class([
        $labelClass,
        'block text-sm font-medium text-stone-700' => $labelClass === null,
    ])>
        {{ $label }}
    </label>
    <input id="{{ $fieldId }}" type="date" wire:model="{{ $model }}"
        @class([
            $inputClass,
            'mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]' =>
                $inputClass === null,
        ]) />
    @error($errorName)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
