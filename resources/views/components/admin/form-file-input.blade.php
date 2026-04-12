@props(['id', 'label', 'accept' => null, 'hint' => null, 'model'])

<div data-admin-form-file-input>
    <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>

    <input id="{{ $id }}" type="file" wire:model="{{ $model }}"
        @if ($accept) accept="{{ $accept }}" @endif
        {{ $attributes->merge([
            'class' =>
                'block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-[#edd2c7] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[#793d3d] hover:file:bg-[#f1bd4d]/60',
        ]) }} />

    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($model)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
