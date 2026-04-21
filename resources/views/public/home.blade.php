<x-layouts::front.main :title="__('home.title')" :show-votings-link="$hasOpenVotings">
    @push('meta')
        <meta name="description" content="{{ __('home.seo_description') }}">
    @endpush

    <!-- Hero Slider -->
    <livewire:hero-slider />

    <!-- Latest Notices Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="sr-only">{{ __('home.title') }}</h1>

        @if ($hasOpenVotings || auth()->check() || $hasActiveConstructions)
            @php($homeCalloutCount = ($hasOpenVotings ? 1 : 0) + (auth()->check() ? 1 : 0) + ($hasActiveConstructions ? 1 : 0))
            @php($stackCalloutActions = $homeCalloutCount === 3)
            <div class="mb-6 grid grid-cols-1 gap-4 {{ $homeCalloutCount === 3 ? 'lg:grid-cols-3' : ($homeCalloutCount === 2 ? 'lg:grid-cols-2' : 'lg:grid-cols-1') }}"
                data-home-callouts>
                @if ($hasOpenVotings)
                    <section
                        class="section-shell overflow-hidden border border-brand-600/35 bg-linear-to-r from-[#edd2c7]/45 via-white to-[#f1bd4d]/20 p-5 sm:p-6"
                        data-home-votings-callout>
                        <div
                            class="{{ $stackCalloutActions ? 'grid h-full grid-cols-[auto,1fr] gap-x-4 gap-y-4' : 'flex h-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between' }}">
                            @if ($stackCalloutActions)
                                <div
                                    class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-brand-600/25 bg-linear-to-br from-[#f1bd4d]/35 via-white to-[#edd2c7]/60 text-[#793d3d] shadow-sm ring-4 ring-white/40">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3h10.5A2.25 2.25 0 0 1 19.5 5.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25A2.25 2.25 0 0 1 6.75 3Zm2.25 4.5h6m-6 3h6m-6 3h3" />
                                    </svg>
                                    <span
                                        class="pointer-events-none absolute -right-1.5 -top-1.5 h-3.5 w-3.5 rounded-full bg-brand-600"></span>
                                </div>
                                <div data-home-votings-copy>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                                        {{ __('home.votings_badge') }}
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                        {{ __('home.votings_title') }}
                                    </h2>
                                    <p class="mt-1.5 text-sm text-gray-600"
                                        data-home-votings-summary>
                                        {{ __('home.votings_summary') }}
                                    </p>
                                </div>

                                <a href="{{ route(\App\SupportedLocales::routeName('votings')) }}"
                                    class="btn-brand col-start-2 inline-flex min-h-11 w-full items-center justify-center whitespace-nowrap"
                                    data-home-votings-cta>
                                    {{ __('home.votings_cta') }}
                                </a>
                            @else
                                <div class="flex items-start gap-4">
                                    <div
                                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-brand-600/25 bg-linear-to-br from-[#f1bd4d]/35 via-white to-[#edd2c7]/60 text-[#793d3d] shadow-sm ring-4 ring-white/40">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.75 3h10.5A2.25 2.25 0 0 1 19.5 5.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25A2.25 2.25 0 0 1 6.75 3Zm2.25 4.5h6m-6 3h6m-6 3h3" />
                                        </svg>
                                        <span
                                            class="pointer-events-none absolute -right-1.5 -top-1.5 h-3.5 w-3.5 rounded-full bg-brand-600"></span>
                                    </div>
                                    <div data-home-votings-copy>
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                                            {{ __('home.votings_badge') }}
                                        </p>
                                        <h2
                                            class="mt-1 text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                            {{ __('home.votings_title') }}
                                        </h2>
                                        <p class="mt-1.5 text-sm text-gray-600"
                                            data-home-votings-summary>
                                            {{ __('home.votings_summary') }}
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route(\App\SupportedLocales::routeName('votings')) }}"
                                    class="btn-brand inline-flex min-h-11 items-center justify-center whitespace-nowrap"
                                    data-home-votings-cta>
                                    {{ __('home.votings_cta') }}
                                </a>
                            @endif
                        </div>
                    </section>
                @endif

                @auth
                    <section
                        class="section-shell overflow-hidden border border-[#793d3d]/25 bg-linear-to-r from-[#edd2c7]/35 via-white to-brand-600/10 p-5 sm:p-6"
                        data-home-profile-callout>
                        <div
                            class="{{ $stackCalloutActions ? 'grid h-full grid-cols-[auto,1fr] gap-x-4 gap-y-4' : 'flex h-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between' }}">
                            @if ($stackCalloutActions)
                                <div
                                    class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#793d3d]/20 bg-linear-to-br from-[#edd2c7]/60 via-white to-brand-600/25 text-[#793d3d] shadow-sm ring-4 ring-white/40">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm-7.5 8.25a7.5 7.5 0 0 1 15 0" />
                                    </svg>
                                    <span
                                        class="pointer-events-none absolute -left-1.5 -bottom-1.5 h-3.5 w-3.5 rounded-full bg-[#f1bd4d]"></span>
                                </div>
                                <div data-home-profile-copy>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                                        {{ __('home.profile_badge') }}
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                        {{ __('home.profile_title') }}
                                    </h2>
                                    <p class="mt-1.5 text-sm text-gray-600" data-home-profile-summary>
                                        {{ __('home.profile_summary') }}
                                    </p>
                                </div>

                                <a href="{{ route(\App\SupportedLocales::routeName('profile')) }}"
                                    class="btn-brand col-start-2 inline-flex min-h-11 w-full items-center justify-center whitespace-nowrap"
                                    data-home-profile-cta>
                                    {{ __('home.profile_cta') }}
                                </a>
                            @else
                                <div class="flex items-start gap-4">
                                    <div
                                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#793d3d]/20 bg-linear-to-br from-[#edd2c7]/60 via-white to-brand-600/25 text-[#793d3d] shadow-sm ring-4 ring-white/40">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm-7.5 8.25a7.5 7.5 0 0 1 15 0" />
                                        </svg>
                                        <span
                                            class="pointer-events-none absolute -left-1.5 -bottom-1.5 h-3.5 w-3.5 rounded-full bg-[#f1bd4d]"></span>
                                    </div>
                                    <div data-home-profile-copy>
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                                            {{ __('home.profile_badge') }}
                                        </p>
                                        <h2
                                            class="mt-1 text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                            {{ __('home.profile_title') }}
                                        </h2>
                                        <p class="mt-1.5 text-sm text-gray-600"
                                            data-home-profile-summary>
                                            {{ __('home.profile_summary') }}
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route(\App\SupportedLocales::routeName('profile')) }}"
                                    class="btn-brand inline-flex min-h-11 items-center justify-center whitespace-nowrap"
                                    data-home-profile-cta>
                                    {{ __('home.profile_cta') }}
                                </a>
                            @endif
                        </div>
                    </section>
                @endauth

                @if ($hasActiveConstructions)
                    <section
                        class="section-shell overflow-hidden border border-[#793d3d]/25 bg-linear-to-r from-[#edd2c7]/35 via-white to-[#f1bd4d]/15 p-5 sm:p-6"
                        data-home-constructions-callout>
                        <div class="grid h-full grid-cols-[auto,1fr] gap-x-4 gap-y-4">
                            <div
                                class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#793d3d]/20 bg-linear-to-br from-[#edd2c7]/60 via-white to-[#f1bd4d]/20 text-[#793d3d] shadow-sm ring-4 ring-white/40">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 21h16.5M5.25 21V9.75m13.5 11.25V6.75m-9 14.25V4.5m-3.75 5.25h7.5" />
                                </svg>
                                <span
                                    class="pointer-events-none absolute -right-1.5 -top-1.5 h-3.5 w-3.5 rounded-full bg-[#f1bd4d]"></span>
                            </div>
                            <div data-home-constructions-copy>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                                    {{ __('home.constructions_badge') }}
                                </p>
                                <h2
                                    class="mt-1 text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                    {{ __('home.constructions_title') }}
                                </h2>
                                <p class="mt-1.5 text-sm text-gray-600"
                                    data-home-constructions-summary>
                                    {{ __('home.constructions_summary') }}
                                </p>
                            </div>

                            <div class="col-start-2 flex flex-col items-start gap-2">
                                @foreach ($activeConstructions as $activeConstruction)
                                    <a href="{{ route(\App\SupportedLocales::routeName('constructions.show'), ['slug' => $activeConstruction->slug]) }}"
                                        class="btn-brand inline-flex min-h-11 items-center justify-center whitespace-nowrap {{ $stackCalloutActions ? 'w-full' : '' }}"
                                        data-home-construction-link="{{ $activeConstruction->slug }}">
                                        {{ $activeConstruction->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
            <div class="space-y-8 lg:col-span-8">
                <section class="section-shell overflow-hidden p-5 sm:p-6" data-home-notices-general>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                        {{ __('home.notices') }}
                    </h2>

                    @if ($generalNotices->isNotEmpty())
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:gap-6"
                            data-latest-notices-general>
                            @foreach ($generalNotices as $notice)
                                <x-front.notice-card :notice="$notice" />
                            @endforeach
                        </div>
                    @else
                        <div
                            class="mt-4 text-center py-10 rounded-xl border border-dashed border-gray-300 bg-gray-50">
                            <p class="text-sm text-gray-600">{{ __('home.no_notices') }}</p>
                        </div>
                    @endif
                </section>

                <section class="section-shell overflow-hidden p-5 sm:p-6"
                    data-home-notices-by-location>

                    @if ($locationNotices->isNotEmpty())
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6"
                            data-latest-notices>
                            @foreach ($locationNotices as $notice)
                                <x-front.notice-card :notice="$notice" />
                            @endforeach
                        </div>
                    @else
                        <div
                            class="mt-4 text-center py-10 rounded-xl border border-dashed border-gray-300 bg-gray-50">
                            <p class="text-sm text-gray-600">{{ __('home.no_notices') }}</p>
                        </div>
                    @endif
                </section>
            </div>

            <div class="space-y-5 lg:col-span-4" data-home-results-and-history>
                @if ($latestVotingWithResults)
                    <section
                        class="rounded-xl border border-[#edd2c7] bg-linear-to-br from-[#edd2c7]/30 via-white to-[#f1bd4d]/10 p-4"
                        data-home-results-block>
                        <div class="flex items-start gap-3" data-home-results-announcement>
                            <div
                                class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-brand-600/25 bg-linear-to-br from-[#f1bd4d]/35 via-white to-[#edd2c7]/60 text-[#793d3d] shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold tracking-tight text-[#793d3d] text-base md:text-lg"
                                    data-home-results-announcement-title>
                                    {{ __('votings.front.results_ended_announcement_title') }}
                                </p>
                                <p class="mt-1 leading-relaxed text-gray-600 text-sm md:text-base"
                                    data-home-results-announcement-body>
                                    {{ __('votings.front.results_ended_announcement_body') }}
                                </p>
                                <a href="{{ route(\App\SupportedLocales::routeName('votings.results'), ['voting' => $latestVotingWithResults->id]) }}"
                                    class="btn-brand mt-3 inline-flex min-h-10 items-center justify-center whitespace-nowrap px-4 py-2 text-sm"
                                    data-results-voting-link="{{ $latestVotingWithResults->id }}">
                                    {{ __('votings.front.results_view_link') }}
                                </a>
                            </div>
                        </div>
                    </section>
                @endif

                <aside
                    class="section-shell overflow-hidden p-4 sm:p-5 lg:flex lg:h-full lg:flex-col"
                    data-home-history>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                        {{ __('home.history_title') }}
                    </h2>
                    <div class="mt-4 space-y-3" data-home-history-images>
                        @foreach ($historyImageUrls as $historyImageUrl)
                            <div
                                class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                                <img src="{{ $historyImageUrl }}" alt=""
                                    aria-hidden="true" class="h-36 w-full object-cover sm:h-44"
                                    loading="lazy" />
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-sm leading-relaxed text-gray-600 rich-content">
                        {!! $historySummary !!}
                    </div>

                    <a href="mailto:{{ $frontPrimaryEmail }}"
                        class="elevated-card mt-4 group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/30 p-4 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 lg:mt-auto"
                        data-home-history-photos-callout>
                        <div class="page-icon-emerald shrink-0 h-10 w-10 rounded-lg">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                                {{ __('home.history_photos_title') }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-600 leading-relaxed">
                                {{ $photoRequestText }}
                            </p>
                        </div>
                        <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-brand-600"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </aside>
            </div>
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
                    class="elevated-card group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/30 p-4 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2">
                    <div class="page-icon-amber shrink-0 h-10 w-10 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                            {{ __('home.explore_notices') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-600 line-clamp-2">
                            {{ __('notices.subtitle') }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-brand-600"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('gallery')) }}"
                    class="elevated-card group flex items-start gap-3 bg-linear-to-br from-white to-[#edd2c7]/20 p-4 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2">
                    <div class="page-icon-emerald shrink-0 h-10 w-10 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-900 group-hover:text-[#793d3d] transition-colors">
                            {{ __('home.explore_gallery') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-600 line-clamp-2">
                            {{ __('gallery.subtitle') }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-brand-600"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('contact')) }}"
                    class="elevated-card group flex items-start gap-3 bg-linear-to-br from-white to-[#f1bd4d]/15 p-4 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2">
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
                        <p class="mt-0.5 text-xs text-gray-600 line-clamp-2">
                            {{ __('contact.subtitle') }}
                        </p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-brand-600"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </section>
    </div>
</x-layouts::front.main>
