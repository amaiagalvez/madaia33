@props([
    'barsHref' => null,
    'barsTitle' => null,
    'barsSrText' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-2']) }}>
    {{ $slot }}

    @if ($barsHref !== null)
        <a href="{{ $barsHref }}" title="{{ $barsTitle ?? '' }}"
            class="inline-flex items-center rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]">
            <flux:icon.bars-3 class="size-4" />
            <span class="sr-only">{{ $barsSrText ?? $barsTitle }}</span>
        </a>
    @endif
</div>
