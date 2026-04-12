@props([
    'active' => false,
])

<span
    {{ $attributes->merge([
        'class' =>
            'inline-flex min-w-16 items-center justify-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ' .
            ($active ? 'text-green-700' : 'text-red-600'),
    ]) }}>
    @if ($active)
        <flux:icon.check-circle class="size-4" />
    @else
        <flux:icon.x-circle class="size-4" />
    @endif
</span>
