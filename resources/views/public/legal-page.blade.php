<x-layouts::front.main :title="__($titleKey)">
    @push('meta')
        <meta name="description" content="{{ __($titleKey) }}">
    @endpush

    <div class="max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14"
        data-legal-page="{{ $pageSlug }}">
        <x-front.public-page-header hero="legal" variant="legal" :show-legal-chips="true" :title="__($titleKey)" />

        <div class="hero-frame px-5 py-5 sm:px-6">
            <div class="space-y-4 text-base leading-relaxed text-gray-700">
                @if ($content)
                    {!! $content !!}
                @else
                    <p class="italic text-gray-500">{{ __($titleKey) }}</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts::front.main>
