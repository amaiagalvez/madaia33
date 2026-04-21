<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>
        {{ filled($title ?? null) ? $title . ' - ' . ($publicSiteName ?? config('app.name', 'Laravel')) : $publicSiteName ?? config('app.name', 'Laravel') }}
    </title>

    @stack('meta')

    @if (request()->route()?->getName())
        @php($__baseName = \App\SupportedLocales::baseRouteName(request()->route()->getName()))
        @php($__routeParameters = request()->route()?->parameters() ?? [])
        @foreach (\App\SupportedLocales::all() as $altLocale)
            @php($__alternateRouteName = \App\SupportedLocales::routeName($__baseName, $altLocale))
            @if (\Illuminate\Support\Facades\Route::has($__alternateRouteName))
                <link rel="alternate" hreflang="{{ $altLocale }}"
                    href="{{ route($__alternateRouteName, $__routeParameters) }}" />
            @endif
        @endforeach
        @php($__defaultRouteName = \App\SupportedLocales::routeName($__baseName, \App\SupportedLocales::default()))
        @if (\Illuminate\Support\Facades\Route::has($__defaultRouteName))
            <link rel="alternate" hreflang="x-default"
                href="{{ route($__defaultRouteName, $__routeParameters) }}" />
        @endif
    @endif

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preload" as="style"
        href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600">
    </noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased flex flex-col public-surface">

    @php($showVotingsLink = $showVotingsLink ?? false)
    @php($isImpersonating = session()->has('impersonator_user_id'))
    @php($showPrivateLink = !auth()->check() || !auth()->user()?->hasOnlyOwnerRole())
    @php($isHomeRoute = request()->routeIs('home.*'))
    @php($isNoticesRoute = request()->routeIs('notices.*'))
    @php($isGalleryRoute = request()->routeIs('gallery.*'))
    @php($isContactRoute = request()->routeIs('contact.*'))
    @php($isVotingsRoute = request()->routeIs('votings.*'))
    @php($isConstructionsRoute = request()->routeIs('constructions.*'))
    @php($isPrivateRoute = request()->routeIs('private.*'))
    @php($activeConstructionsNav = $activeConstructionsNav ?? collect())
    @php($currentConstructionSlug = (string) request()->route('slug', ''))

    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-60 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-900 focus:shadow-md focus:ring-2 focus:ring-gray-500 focus:outline-none">
        {{ __('general.nav.main') }}
    </a>

    {{-- Brand top bar --}}
    <div class="h-1 bg-linear-to-r from-[#793d3d] via-brand-600 to-[#f1bd4d]" aria-hidden="true">
    </div>

    {{-- Header / Navigation --}}
    <header id="public-header"
        class="public-header sticky top-0 z-70 isolate border-b border-gray-200 bg-white pt-[env(safe-area-inset-top)] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="header-shell flex items-center justify-between gap-2 min-h-16 transition-all duration-200">

                {{-- Logo / Site name (left) --}}
                <x-front.public-brand-link />

                {{-- Desktop navigation (center, hidden on mobile/tablet) --}}
                <nav class="hidden flex-1 items-center justify-center gap-1 px-2 py-1.5 transition-all duration-200 md:flex"
                    aria-label="{{ __('general.nav.main') ?? 'Nabigazio nagusia' }}">
                    <a href="{{ route(\App\SupportedLocales::routeName('home')) }}"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isHomeRoute ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                        @if ($isHomeRoute) aria-current="page" @endif>
                        {{ __('general.nav.home') }}
                    </a>
                    <a href="{{ route(\App\SupportedLocales::routeName('notices')) }}"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isNoticesRoute ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                        @if ($isNoticesRoute) aria-current="page" @endif>
                        {{ __('general.nav.notices') }}
                    </a>
                    <a href="{{ route(\App\SupportedLocales::routeName('gallery')) }}"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isGalleryRoute ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                        @if ($isGalleryRoute) aria-current="page" @endif>
                        {{ __('general.nav.gallery') }}
                    </a>
                    <a href="{{ route(\App\SupportedLocales::routeName('contact')) }}"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isContactRoute ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                        @if ($isContactRoute) aria-current="page" @endif>
                        {{ __('general.nav.contact') }}
                    </a>
                    @if ($showVotingsLink)
                        <a href="{{ route(\App\SupportedLocales::routeName('votings')) }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isVotingsRoute ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                            @if ($isVotingsRoute) aria-current="page" @endif>
                            {{ __('general.nav.votings') }}
                        </a>
                    @endif
                    @foreach ($activeConstructionsNav as $activeConstructionNav)
                        @php($isCurrentConstruction = $isConstructionsRoute && $currentConstructionSlug === $activeConstructionNav->slug)
                        <a href="{{ route(\App\SupportedLocales::routeName('constructions.show'), ['slug' => $activeConstructionNav->slug]) }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isCurrentConstruction ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                            data-nav-construction-link="{{ $activeConstructionNav->slug }}"
                            @if ($isCurrentConstruction) aria-current="page" @endif>
                            {{ $activeConstructionNav->title }}
                        </a>
                    @endforeach
                    @if ($showPrivateLink)
                        <a href="{{ route(\App\SupportedLocales::routeName('private')) }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $isPrivateRoute ? 'bg-[#793d3d] text-white shadow-sm shadow-[#793d3d]/25' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                            @if ($isPrivateRoute) aria-current="page" @endif>
                            {{ __('general.nav.private') }}
                        </a>
                    @endif
                </nav>

                {{-- Right section: Language switcher + Mobile menu button --}}
                <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3">
                    @auth
                        <div class="hidden md:block">
                            <x-shared.desktop-user-menu />
                        </div>
                    @endauth

                    {{-- Language switcher (always visible) --}}
                    <livewire:language-switcher />

                    {{-- Mobile menu button (hidden on md and above) --}}
                    <button type="button"
                        class="md:hidden min-h-11 min-w-11 rounded-md p-2 text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2"
                        aria-label="Menua ireki / Abrir menú" data-hamburger-button
                        aria-controls="public-mobile-menu" x-data="{ mobileMenuOpen: false }"
                        x-bind:aria-expanded="mobileMenuOpen ? 'true' : 'false'"
                        @mobile-menu-state-changed.window="mobileMenuOpen = !!$event.detail?.open"
                        @click="$dispatch('toggle-mobile-menu'); setTimeout(() => { const firstItem = document.querySelector('[data-mobile-menu] [data-first-menu-item]'); if (firstItem && getComputedStyle(firstItem).display !== 'none') { firstItem.focus(); } }, 180)">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile navigation (visible only on md breakpoint below) --}}
        <div id="public-mobile-menu"
            class="md:hidden border-t border-gray-200 bg-white max-h-[calc(100vh-4rem-env(safe-area-inset-top))] overflow-y-auto"
            data-mobile-menu x-cloak x-data="mobileMenu()"
            @toggle-mobile-menu.window="toggleMenu()"
            @keydown.escape.window="if (open) { toggleMenu() }" @keydown.tab="trapMenuFocus($event)"
            x-effect="if (open) { focusFirstMenuItem() }" x-show="open" x-transition>
            <nav class="px-4 py-3 flex flex-col gap-1" x-ref="mobileNav"
                aria-label="{{ __('general.nav.main') ?? 'Nabigazio nagusia' }}">
                <a href="{{ route(\App\SupportedLocales::routeName('home')) }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isHomeRoute ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                    @if ($isHomeRoute) aria-current="page" @endif
                    data-first-menu-item x-ref="firstMenuItem">
                    {{ __('general.nav.home') }}
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('notices')) }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isNoticesRoute ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                    @if ($isNoticesRoute) aria-current="page" @endif
                    data-mobile-notices-link>
                    {{ __('general.nav.notices') }}
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('gallery')) }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isGalleryRoute ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                    @if ($isGalleryRoute) aria-current="page" @endif>
                    {{ __('general.nav.gallery') }}
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('contact')) }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isContactRoute ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                    @if ($isContactRoute) aria-current="page" @endif>
                    {{ __('general.nav.contact') }}
                </a>
                @if ($showVotingsLink)
                    <a href="{{ route(\App\SupportedLocales::routeName('votings')) }}"
                        class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isVotingsRoute ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                        @if ($isVotingsRoute) aria-current="page" @endif>
                        {{ __('general.nav.votings') }}
                    </a>
                @endif
                @foreach ($activeConstructionsNav as $activeConstructionNav)
                    @php($isCurrentConstruction = $isConstructionsRoute && $currentConstructionSlug === $activeConstructionNav->slug)
                    <a href="{{ route(\App\SupportedLocales::routeName('constructions.show'), ['slug' => $activeConstructionNav->slug]) }}"
                        class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isCurrentConstruction ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                        data-nav-construction-link="{{ $activeConstructionNav->slug }}"
                        @if ($isCurrentConstruction) aria-current="page" @endif>
                        {{ $activeConstructionNav->title }}
                    </a>
                @endforeach
                @if ($showPrivateLink)
                    <a href="{{ route(\App\SupportedLocales::routeName('private')) }}"
                        class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-[#edd2c7]/45 transition-colors min-h-11 {{ $isPrivateRoute ? 'bg-[#edd2c7] text-[#793d3d] font-semibold' : 'text-stone-700 hover:text-[#793d3d]' }}"
                        @if ($isPrivateRoute) aria-current="page" @endif>
                        {{ __('general.nav.private') }}
                    </a>
                @endif

                @auth
                    <div class="mt-2 border-t border-gray-200 pt-3">
                        <p class="px-3 text-sm font-medium text-stone-700">{{ auth()->user()->name }}
                        </p>
                        <a href="{{ route(\App\SupportedLocales::routeName('profile')) }}"
                            class="mt-2 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-[#edd2c7]/45 hover:text-[#793d3d]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ __('profile.title') }}
                        </a>
                        <form method="POST"
                            action="{{ $isImpersonating ? route('admin.users.stop_impersonation') : route('logout') }}"
                            class="mt-2 px-3">
                            @csrf
                            <button type="submit"
                                class="inline-flex min-h-11 w-full items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $isImpersonating ? 'border border-red-700 bg-red-600 text-white hover:bg-red-700' : 'border border-stone-200 bg-white text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                                {{ $isImpersonating ? __('admin.users.back_to_my_user') : __('admin.logout') }}
                            </button>
                        </form>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Main content --}}
    <main id="main-content" class="relative flex-1 overflow-x-clip" tabindex="-1">
        @if ($isImpersonating)
            <div class="mx-auto mb-4 mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-sm font-semibold text-red-700">
                        {{ __('admin.users.impersonating_as', ['name' => auth()->user()?->name, 'email' => auth()->user()?->email]) }}
                    </p>
                </div>
            </div>
        @endif
        <div aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-20 -z-10 h-72 w-72 rounded-full bg-[#f1bd4d]/20 blur-3xl">
        </div>
        <div aria-hidden="true"
            class="pointer-events-none absolute -right-24 top-112 -z-10 h-80 w-80 rounded-full bg-brand-600/18 blur-3xl">
        </div>
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-linear-to-b from-white/90 to-transparent">
        </div>
        <div class="animate-soft-rise">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer --}}
    <footer
        class="mt-auto border-t border-gray-200 bg-gray-50/90 pb-[env(safe-area-inset-bottom)]">
        <div class="h-0.5 bg-linear-to-r from-[#793d3d] via-brand-600 to-[#f1bd4d]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div
                class="flex flex-col items-stretch gap-5 sm:gap-6 lg:flex-row lg:items-center lg:justify-between">
                <x-front.public-brand-link
                    class="glass-panel w-full max-w-md px-4 py-3 sm:w-auto" />
                <nav class="grid gap-3 sm:grid-cols-2" aria-label="Footer">
                    <a href="{{ route(\App\SupportedLocales::routeName('privacy-policy')) }}"
                        class="hero-frame px-4 py-3 transition-colors hover:border-brand-600/60 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ __('general.footer.privacy_policy') }}</p>
                    </a>
                    <a href="{{ route(\App\SupportedLocales::routeName('legal-notice')) }}"
                        class="hero-frame px-4 py-3 transition-colors hover:border-brand-600/60 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ __('general.footer.legal_notice') }}</p>
                    </a>
                    <a href="{{ route(\App\SupportedLocales::routeName('cookie-policy')) }}"
                        class="hero-frame px-4 py-3 transition-colors hover:border-brand-600/60 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 sm:col-span-2">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ __('general.footer.cookie_policy') }}</p>
                    </a>
                </nav>
                <a href="mailto:info@amaia.eus"
                    class="inline-flex items-center self-end lg:self-center focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2"
                    aria-label="Contact with Amaia">
                    <img src="{{ asset('amaia-footer.png') }}" alt="Amaia Galvez Itarte"
                        aria-hidden="true" class="h-10 w-auto object-contain sm:h-11" />
                </a>
            </div>
        </div>
    </footer>

    <div x-data="{
        accepted: false,
        init() {
            this.accepted = document.cookie.split(';').some((entry) => entry.trim().startsWith('madaia_cookie_consent=1'));
        },
        dismiss() {
            document.cookie = 'madaia_cookie_consent=1; Max-Age=31536000; Path=/; SameSite=Lax';
            this.accepted = true;
        }
    }" x-show="!accepted" x-cloak
        class="fixed inset-x-0 bottom-0 z-80 border-t border-brand-600/30 bg-stone-900 px-4 py-4 shadow-lg"
        data-cookie-consent-banner>
        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-3 sm:flex-row sm:items-start sm:gap-6">
            <p class="flex-1 text-sm text-stone-300" data-cookie-consent-message>
                {{ __('general.cookies.banner_message') }}
            </p>

            <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route(\App\SupportedLocales::routeName('cookie-policy')) }}"
                    class="inline-flex min-h-9 items-center justify-center rounded-md border border-brand-600/40 px-4 py-2 text-sm font-medium text-[#793d3d] transition hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2"
                    data-cookie-policy-link>
                    {{ __('general.cookies.more_info') }}
                </a>

                <button type="button" @click="dismiss()"
                    class="inline-flex min-h-9 items-center justify-center rounded-md bg-[#793d3d] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#5e2f2f] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2"
                    data-cookie-consent-understood>
                    {{ __('general.cookies.understood') }}
                </button>
            </div>
        </div>
    </div>

    @fluxScripts
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mobileMenu', () => ({
                open: false,

                init() {
                    this.dispatchMenuState();
                },

                dispatchMenuState() {
                    window.dispatchEvent(new CustomEvent(
                        'mobile-menu-state-changed', {
                            detail: {
                                open: this.open,
                            },
                        }));
                },

                toggleMenu() {
                    this.open = !this.open;
                    this.dispatchMenuState();

                    if (!this.open) {
                        this.$nextTick(() => document.querySelector(
                            '[data-hamburger-button]')?.focus());
                    }
                },

                focusFirstMenuItem() {
                    this.$nextTick(() => setTimeout(() => this.$refs.firstMenuItem
                        ?.focus(), 200));
                },

                trapMenuFocus(event) {
                    if (!this.open || event.key !== 'Tab') {
                        return;
                    }

                    const focusableElements = Array.from(
                        this.$refs.mobileNav.querySelectorAll(
                            'a, button, [tabindex]:not([tabindex="-1"])')
                    );

                    if (focusableElements.length === 0) {
                        return;
                    }

                    const first = focusableElements[0];
                    const last = focusableElements[focusableElements.length - 1];
                    const active = document.activeElement;

                    if (!event.shiftKey && active === last) {
                        event.preventDefault();
                        first.focus();

                        return;
                    }

                    if (event.shiftKey && active === first) {
                        event.preventDefault();
                        last.focus();
                    }
                },
            }));
        });
    </script>
</body>

</html>
