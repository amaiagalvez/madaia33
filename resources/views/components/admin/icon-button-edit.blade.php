@props([
    'title' => null,
])

<button
    type="button"
    data-admin-action="edit"
    title="{{ $title ?? __('general.buttons.edit') }}"
    {{ $attributes->merge([
        'class' => 'rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]',
    ]) }}
>
    <flux:icon.pencil-square class="size-4" />
</button>
