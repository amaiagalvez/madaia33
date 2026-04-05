<x-layouts::public :title="__($titleKey)">
    @push('meta')
        <meta name="description" content="{{ __($titleKey) }} - {{ config('app.name') }}">
    @endpush

    <div class="max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-12" data-legal-page="{{ $pageSlug }}">
        <h1 class="mb-6 text-2xl md:text-3xl font-bold text-gray-900">
            {{ __($titleKey) }}
        </h1>

        <div class="space-y-4 text-base leading-relaxed text-gray-700">
            @if ($content)
                {!! nl2br(e($content)) !!}
            @else
                <p class="italic text-gray-500">{{ __($titleKey) }}</p>
            @endif
        </div>
    </div>
</x-layouts::public>
