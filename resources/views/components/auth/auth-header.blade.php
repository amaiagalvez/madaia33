@props(['title', 'description' => null])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="text-2xl font-bold tracking-tight text-gray-900 md:text-3xl">
        {{ $title }}
    </flux:heading>

    @if ($description)
        <flux:subheading class="mt-3 text-sm leading-relaxed text-gray-600">
            {{ $description }}
        </flux:subheading>
    @endif
</div>
