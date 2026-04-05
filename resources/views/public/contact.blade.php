<x-layouts::public :title="__('contact.title')">
    @push('meta')
        <meta name="description" content="{{ __('contact.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="contact">
        <section class="mb-8 section-shell overflow-hidden p-6 sm:p-8" data-page-hero="contact">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-end">
                <div class="flex items-start gap-4">
                    <div class="page-icon-indigo">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div>
                        <div class="mb-3 flex flex-wrap gap-2">
                            <span
                                class="feature-chip border-indigo-100 bg-indigo-50 text-indigo-700">{{ __('contact.support_badge') }}</span>
                            <span
                                class="feature-chip">{{ __('general.footer.privacy_policy') }}</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">
                            {{ __('contact.title') }}</h1>
                        <p class="mt-1 max-w-2xl text-sm sm:text-base text-gray-600">
                            {{ __('contact.subtitle') }}</p>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
                            {{ __('contact.trust_note') }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="stat-tile">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                            {{ __('contact.support_badge') }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">
                            {{ __('contact.support_summary') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-start">
            <aside class="hero-frame px-5 py-6 sm:px-6">
                <span
                    class="feature-chip border-indigo-100 bg-indigo-50 text-indigo-700">{{ __('contact.support_badge') }}</span>
                <h2 class="mt-4 text-2xl font-bold tracking-tight text-gray-900">
                    {{ __('contact.support_title') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-gray-600">
                    {{ __('contact.support_summary') }}</p>
                <p class="mt-3 text-sm leading-relaxed text-gray-500">
                    {{ __('contact.trust_note') }}</p>

                <div class="mt-6 grid gap-3">
                    <div class="stat-tile">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ __('contact.benefits.response') }}</p>
                    </div>
                    <div class="stat-tile">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ __('contact.benefits.secure') }}</p>
                    </div>
                    <div class="stat-tile">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ __('contact.benefits.clarity') }}</p>
                    </div>
                </div>
            </aside>

            <div class="hero-frame p-5 sm:p-6">
                <div class="max-w-3xl">
                    <livewire:contact-form />
                </div>
            </div>
        </div>
    </div>
</x-layouts::public>
