@props([
    'state' => 'neutral',
    'title' => null,
])

@php
    $stateClasses = match ($state) {
        'success' => 'text-green-700 hover:border-green-300 hover:bg-green-100',
        'danger' => 'text-red-600 hover:border-red-300 hover:bg-red-100',
        default => 'text-gray-700 hover:border-gray-300 hover:bg-gray-100',
    };
@endphp

<button
    type="button"
    data-admin-action-link="confirm"
    title="{{ $title }}"
    {{ $attributes->merge([
        'class' => 'inline-flex min-w-16 items-center justify-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition-colors '.$stateClasses,
    ]) }}
>
    {{ $slot }}
</button>
