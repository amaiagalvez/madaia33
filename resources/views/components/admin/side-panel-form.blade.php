@props([
    'section' => null,
    'cardId' => null,
    'cancelAction',
    'cancelLabel' => null,
])

<div class="fixed inset-0 z-40"
    @if ($section) data-section="{{ $section }}" @endif>
    <button type="button" wire:click="{{ $cancelAction }}"
        class="admin-slideover-backdrop absolute inset-0 bg-black/30"
        aria-label="{{ $cancelLabel ?? __('general.buttons.cancel') }}"></button>

    <div @if ($cardId) id="{{ $cardId }}" @endif
        class="admin-slideover-panel absolute inset-y-0 right-0 z-50 h-full w-full max-w-4xl overflow-y-auto bg-white p-6 shadow-2xl"
        data-admin-side-panel-form>
        {{ $slot }}
    </div>
</div>
