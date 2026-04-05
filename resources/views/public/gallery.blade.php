<x-layouts::public :title="__('gallery.title')">
    @push('meta')
        <meta name="description" content="{{ __('gallery.seo_description') }}">
    @endpush

    @php
        $imageCount = \App\Models\Image::query()->count();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="gallery">
        <section class="mb-8 section-shell overflow-hidden p-6 sm:p-8" data-page-hero="gallery">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-end">
                <div class="flex items-start gap-4">
                    <div class="page-icon-emerald">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="mb-3 flex flex-wrap gap-2">
                            <span
                                class="feature-chip border-emerald-100 bg-emerald-50 text-emerald-700">{{ __('gallery.editorial_badge') }}</span>
                            <span
                                class="feature-chip border-emerald-100 bg-emerald-50 text-emerald-700">{{ __('gallery.title') }}</span>
                            <span
                                class="feature-chip">{{ __('hero_slider.view_more_images') }}</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">
                            {{ __('gallery.title') }}</h1>
                        <p class="mt-1 max-w-2xl text-sm sm:text-base text-gray-600">
                            {{ __('gallery.subtitle') }}</p>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
                            {{ __('gallery.editorial_summary') }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_140px]">
                    <div class="stat-tile">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                            {{ __('gallery.title') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $imageCount }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('gallery.mosaic_note') }}</p>
                    </div>
                    <div class="grid gap-3">
                        <div
                            class="h-24 rounded-2xl bg-linear-to-br from-emerald-200/70 to-emerald-50 shadow-inner">
                        </div>
                        <div
                            class="h-24 rounded-2xl bg-linear-to-br from-indigo-100 to-cyan-50 shadow-inner">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <livewire:image-gallery />
    </div>
</x-layouts::public>
