<x-layouts::public :title="__('notices.title')">
    @push('meta')
        <meta name="description" content="{{ __('notices.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="notices">
        <section class="mb-8 section-shell overflow-hidden p-6 sm:p-8" data-page-hero="notices">
            <div class="flex items-start gap-4">
                <div class="page-icon-amber">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <div>
                    <div class="mb-3 flex flex-wrap gap-2">
                        <span
                            class="feature-chip border-amber-100 bg-amber-50 text-amber-700">{{ __('notices.editorial_badge') }}</span>
                        <span
                            class="feature-chip border-amber-100 bg-amber-50 text-amber-700">{{ __('notices.title') }}</span>
                        <span class="feature-chip">{{ __('notices.portal') }}</span>
                        <span class="feature-chip">{{ __('notices.garage') }}</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">
                        {{ __('notices.title') }}</h1>
                    <p class="mt-1 max-w-2xl text-sm sm:text-base text-gray-600">
                        {{ __('notices.subtitle') }}</p>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
                        {{ __('notices.editorial_summary') }}</p>
                </div>
            </div>
        </section>

        <livewire:public-notices />
    </div>
</x-layouts::public>
