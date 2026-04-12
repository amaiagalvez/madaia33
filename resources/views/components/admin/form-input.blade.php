@props([
    'name',
    'label',
    'type' => 'text',
    'model' => null,
    'id' => null,
    'min' => null,
    'max' => null,
    'disabled' => false,
    'readonly' => false,
])

@php
    $fieldId = $id ?? $name;
    $wireModel = $model ?? $name;
@endphp

<div data-admin-form-input>
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-stone-700">
        {{ $label }}
    </label>
    <input id="{{ $fieldId }}" type="{{ $type }}" wire:model="{{ $wireModel }}"
        @if ($min !== null) min="{{ $min }}" @endif
        @if ($max !== null) max="{{ $max }}" @endif
        @disabled($disabled) @readonly($readonly)
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
    @error($wireModel)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
