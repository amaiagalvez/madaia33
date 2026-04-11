@props([
    'title' => null,
])

<button
    type="button"
    data-admin-action="delete"
    title="{{ $title ?? __('general.buttons.delete') }}"
    {{ $attributes->merge([
        'class' => 'rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-red-200 hover:bg-red-50 hover:text-[#d9755b]',
    ]) }}
>
    <flux:icon.trash class="size-4" />
</button>
