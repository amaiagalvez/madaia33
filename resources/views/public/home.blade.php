<x-layouts::public :title="__('home.title')">
    @push('meta')
        <meta name="description" content="{{ __('home.seo_description') }}">
    @endpush

    <!-- Hero Slider -->
    <livewire:hero-slider />

    <!-- Latest Notices Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="sr-only">{{ __('home.title') }}</h1>

        <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
            <div class="space-y-8 lg:col-span-8">
                <section class="section-shell overflow-hidden p-5 sm:p-6" data-home-notices-general>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                        {{ __('home.notices') }}
                    </h2>

                    @if ($generalNotices->isNotEmpty())
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6"
                            data-latest-notices-general>
                            @foreach ($generalNotices as $notice)
                                <x-notice-card :notice="$notice" />
                            @endforeach
                        </div>
                    @else
                        <div
                            class="mt-4 text-center py-10 rounded-xl border border-dashed border-gray-300 bg-gray-50">
                            <p class="text-sm text-gray-500">{{ __('home.no_notices') }}</p>
                        </div>
                    @endif
                </section>

                <section class="section-shell overflow-hidden p-5 sm:p-6"
                    data-home-notices-by-location>

                    @if ($locationNotices->isNotEmpty())
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6"
                            data-latest-notices>
                            @foreach ($locationNotices as $notice)
                                <x-notice-card :notice="$notice" />
                            @endforeach
                        </div>
                    @else
                        <div
                            class="mt-4 text-center py-10 rounded-xl border border-dashed border-gray-300 bg-gray-50">
                            <p class="text-sm text-gray-500">{{ __('home.no_notices') }}</p>
                        </div>
                    @endif
                </section>
            </div>

            <aside
                class="section-shell overflow-hidden p-4 sm:p-5 lg:col-span-4 lg:flex lg:h-full lg:flex-col"
                data-home-history>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                    {{ __('home.history_title') }}
                </h2>
                <div class="mt-4 space-y-3" data-home-history-images>
                    @foreach ($historyImageUrls as $historyImageUrl)
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                            <img src="{{ $historyImageUrl }}" alt="{{ __('home.history_title') }}"
                                class="h-36 w-full object-cover sm:h-44" loading="lazy" />
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-sm leading-relaxed text-gray-600">
                    {!! $historySummary !!}
                </p>

                <a href="mailto:admin@madaia.eus"
                    class="elevated-card mt-4 group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/30 p-4 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2 lg:mt-auto"
                    data-home-history-photos-callout>
                    <div class="page-icon-emerald shrink-0 h-10 w-10 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                            {{ __('home.history_photos_title') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 leading-relaxed">
                            {{ __('home.history_photos_summary', ['email' => 'admin@madaia.eus']) }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-[#d9755b]"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </aside>
        </div>

        @if ($showViewAllButton)
            <div class="mt-8 text-center">
                <a href="{{ route(\App\SupportedLocales::routeName('notices')) }}"
                    class="btn-brand">
                    {{ __('home.view_all') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        @endif

        <section class="section-shell mt-8 overflow-hidden p-6 sm:p-8" data-page-hero="home">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" data-home-quick-links>
                <a href="{{ route(\App\SupportedLocales::routeName('notices')) }}"
                    class="elevated-card group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/30 p-4 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    <div class="page-icon-amber shrink-0 h-10 w-10 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                            {{ __('home.explore_notices') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">
                            {{ __('notices.subtitle') }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-[#d9755b]"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('gallery')) }}"
                    class="elevated-card group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/20 p-4 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    <div class="page-icon-emerald shrink-0 h-10 w-10 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                            {{ __('home.explore_gallery') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">
                            {{ __('gallery.subtitle') }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-[#d9755b]"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('contact')) }}"
                    class="elevated-card group flex items-start gap-3 bg-linear-to-br from-white to-[#f1bd4d]/15 p-4 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    <div class="page-icon-indigo shrink-0 h-10 w-10 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                            {{ __('home.contact_us') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">
                            {{ __('contact.subtitle') }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-[#d9755b]"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </section>
    </div>
</x-layouts::public>
