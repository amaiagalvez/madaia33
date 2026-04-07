<x-layouts::public :title="__('gallery.title')">
    @push('meta')
        <meta name="description" content="{{ __('gallery.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="gallery">
        <section class="mb-8 section-shell overflow-hidden p-6 sm:p-8" data-page-hero="gallery">
            <div class="flex items-start gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">
                        {{ __('gallery.title') }}</h1>
                    <p class="mt-1 max-w-2xl text-sm sm:text-base text-gray-600">
                        {{ __('gallery.subtitle') }}</p>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
                        {{ __('gallery.editorial_summary') }}</p>
                </div>
            </div>
        </section>

        <livewire:image-gallery />
    </div>
</x-layouts::public>
