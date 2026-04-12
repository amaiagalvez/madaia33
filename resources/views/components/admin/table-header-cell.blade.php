@props([
    'sortable' => false,
])

<th scope="col" data-admin-table-header
    {{ $attributes->class([
        'px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500',
        'cursor-pointer hover:text-[#793d3d]' => $sortable,
    ]) }}>
    {{ $slot }}
</th>
