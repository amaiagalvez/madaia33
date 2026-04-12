@props([
    'active' => false,
    'key' => null,
])

<button type="button"
    @if ($key) data-admin-filter-button="{{ $key }}" @endif
    {{ $attributes->class([
        'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
        'border-[#d9755b] bg-[#d9755b] text-white' => $active,
        'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' => !$active,
    ]) }}>
    {{ $slot }}
</button>
