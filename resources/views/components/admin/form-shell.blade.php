@props([
    'title' => null,
])

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    @if (filled($title))
        <h3 class="mb-4 text-lg font-semibold text-stone-900">{{ $title }}</h3>
    @endif

    {{ $slot }}
</div>
