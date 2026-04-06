<x-layouts::public :title="__('contact.title')">
    @push('meta')
        <meta name="description" content="{{ __('contact.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="contact">
        <section class="mb-8 section-shell overflow-hidden p-6 sm:p-8" data-page-hero="contact">
            <div>
                <div class="flex items-start gap-4">
                    <div class="page-icon-indigo">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">
                            {{ __('contact.title') }}</h1>
                    </div>
                </div>
            </div>
        </section>

        <div class="hero-frame p-5 sm:p-6">
            <div class="max-w-3xl">
                <livewire:contact-form />
            </div>
        </div>
    </div>
</x-layouts::public>
