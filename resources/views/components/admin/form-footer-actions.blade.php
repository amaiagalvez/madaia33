@props([
    'showDefaultButtons' => false,
    'isEditing' => false,
    'cancelAction' => null,
    'saveLabel' => null,
    'cancelLabel' => null,
    'showCancelButton' => true,
])

<div data-admin-form-footer-actions {{ $attributes->class(['mt-6 flex flex-wrap gap-3']) }}>
    @if ($showDefaultButtons)
        <button type="submit"
            class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
            {{ $saveLabel ?? ($isEditing ? __('general.buttons.save') : __('general.buttons.create_new')) }}
        </button>

        {{ $slot }}

        @if ($showCancelButton)
            <button type="button"
                @if ($cancelAction) wire:click="{{ $cancelAction }}" @endif
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                {{ $cancelLabel ?? __('general.buttons.cancel') }}
            </button>
        @endif
    @else
        {{ $slot }}
    @endif
</div>
