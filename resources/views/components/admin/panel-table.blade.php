@props([
    'tableClass' => 'min-w-full divide-y divide-gray-200',
])

<div
    {{ $attributes->class(['overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm']) }}>
    <table class="{{ $tableClass }}">
        {{ $slot }}
    </table>
</div>
