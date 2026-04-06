<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>
        {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    @stack('meta')

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased flex flex-col public-surface">

    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-60 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-900 focus:shadow-md focus:ring-2 focus:ring-gray-500 focus:outline-none">
        {{ __('general.nav.main') }}
    </a>

    {{-- Brand top bar --}}
    <div class="h-1 bg-linear-to-r from-amber-700 via-orange-700 to-emerald-700" aria-hidden="true">
    </div>

    {{-- Header / Navigation --}}
    <header id="public-header"
        class="public-header sticky top-0 z-[70] isolate border-b border-gray-200 bg-white pt-[env(safe-area-inset-top)] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="header-shell flex items-center justify-between gap-2 min-h-16 transition-all duration-200">

                {{-- Logo / Site name (left) --}}
                <a href="{{ route('home') }}"
                    class="flex min-w-0 items-center gap-2 sm:gap-3 rounded-sm transition-colors hover:text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2">
                    <img src="{{ asset('storage/madaia33/madaia33.png') }}"
                        alt="{{ config('app.name', 'Madaia') }} logo"
                        class="header-brand-mark h-10 w-10 sm:h-12 sm:w-12 rounded-2xl object-cover shadow-lg shadow-amber-900/20 transition-all duration-200" />
                    <span class="hidden sm:flex flex-col leading-none">
                        <span
                            class="text-lg font-semibold text-gray-900">{{ config('app.name', 'Madaia') }}</span>
                        <span
                            class="header-brand-subtitle text-[11px] uppercase tracking-[0.18em] text-stone-500 transition-all duration-200">Labeaga
                            33, Urretxu</span>
                    </span>
                </a>

                {{-- Desktop navigation (center, hidden on mobile/tablet) --}}
                <nav class="hidden flex-1 justify-center md:flex"
                    aria-label="{{ __('general.nav.main') ?? 'Nabigazio nagusia' }}">
                    <div
                        class="header-nav-panel glass-panel flex items-center gap-1 px-2 py-1.5 transition-all duration-200">
                        <a href="{{ route('notices') }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 {{ request()->routeIs('notices') ? 'bg-stone-700 text-white shadow-sm shadow-stone-900/20' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}"
                            aria-current="{{ request()->routeIs('notices') ? 'page' : 'false' }}">
                            {{ __('general.nav.notices') }}
                        </a>
                        <a href="{{ route('gallery') }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 {{ request()->routeIs('gallery') ? 'bg-stone-700 text-white shadow-sm shadow-stone-900/20' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}"
                            aria-current="{{ request()->routeIs('gallery') ? 'page' : 'false' }}">
                            {{ __('general.nav.gallery') }}
                        </a>
                        <a href="{{ route('contact') }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 {{ request()->routeIs('contact') ? 'bg-stone-700 text-white shadow-sm shadow-stone-900/20' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}"
                            aria-current="{{ request()->routeIs('contact') ? 'page' : 'false' }}">
                            {{ __('general.nav.contact') }}
                        </a>
                        <a href="{{ route('private') }}"
                            class="rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 {{ request()->routeIs('private') ? 'bg-stone-700 text-white shadow-sm shadow-stone-900/20' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}"
                            aria-current="{{ request()->routeIs('private') ? 'page' : 'false' }}">
                            {{ __('general.nav.private') }}
                        </a>
                    </div>
                </nav>

                {{-- Right section: Language switcher + Mobile menu button --}}
                <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3">
                    {{-- Language switcher (always visible) --}}
                    <livewire:language-switcher />

                    {{-- Mobile menu button (hidden on md and above) --}}
                    <button type="button"
                        class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors min-h-11 min-w-11"
                        aria-label="Menua ireki / Abrir menú" data-hamburger-button x-data
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
        <div class="md:hidden border-t border-gray-200 bg-white max-h-[calc(100vh-4rem-env(safe-area-inset-top))] overflow-y-auto"
            data-mobile-menu x-cloak x-data="mobileMenu()"
            @toggle-mobile-menu.window="toggleMenu()"
            @keydown.escape.window="if (open) { toggleMenu() }" @keydown.tab="trapMenuFocus($event)"
            x-effect="if (open) { focusFirstMenuItem() }" x-show="open" x-transition>
            <nav class="px-4 py-3 flex flex-col gap-1" x-ref="mobileNav"
                aria-label="{{ __('general.nav.main') ?? 'Nabigazio nagusia' }}">
                <a href="{{ route('notices') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-stone-100 transition-colors min-h-11 {{ request()->routeIs('notices') ? 'bg-stone-200 text-stone-900 font-semibold' : 'text-stone-700' }}"
                    aria-current="{{ request()->routeIs('notices') ? 'page' : 'false' }}"
                    data-first-menu-item x-ref="firstMenuItem">
                    {{ __('general.nav.notices') }}
                </a>
                <a href="{{ route('gallery') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-stone-100 transition-colors min-h-11 {{ request()->routeIs('gallery') ? 'bg-stone-200 text-stone-900 font-semibold' : 'text-stone-700' }}"
                    aria-current="{{ request()->routeIs('gallery') ? 'page' : 'false' }}">
                    {{ __('general.nav.gallery') }}
                </a>
                <a href="{{ route('contact') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-stone-100 transition-colors min-h-11 {{ request()->routeIs('contact') ? 'bg-stone-200 text-stone-900 font-semibold' : 'text-stone-700' }}"
                    aria-current="{{ request()->routeIs('contact') ? 'page' : 'false' }}">
                    {{ __('general.nav.contact') }}
                </a>
                <a href="{{ route('private') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-stone-100 transition-colors min-h-11 {{ request()->routeIs('private') ? 'bg-stone-200 text-stone-900 font-semibold' : 'text-stone-700' }}"
                    aria-current="{{ request()->routeIs('private') ? 'page' : 'false' }}">
                    {{ __('general.nav.private') }}
                </a>
            </nav>
        </div>
    </header>

    {{-- Main content --}}
    <main id="main-content" class="relative flex-1 overflow-x-clip" tabindex="-1">
        <div aria-hidden="true"
            class="pointer-events-none absolute left-[-8rem] top-20 -z-10 h-72 w-72 rounded-full bg-amber-200/25 blur-3xl">
        </div>
        <div aria-hidden="true"
            class="pointer-events-none absolute right-[-6rem] top-[28rem] -z-10 h-80 w-80 rounded-full bg-emerald-200/20 blur-3xl">
        </div>
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-linear-to-b from-white/90 to-transparent">
        </div>
        <div class="animate-soft-rise">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer --}}
    <footer class="mt-auto border-t border-gray-200 bg-gray-50/90 pb-[env(safe-area-inset-bottom)]">
        <div class="h-0.5 bg-linear-to-r from-stone-600 via-amber-700 to-emerald-700"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div
                class="flex flex-col items-stretch gap-5 sm:gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div
                    class="glass-panel flex w-full max-w-md items-center gap-4 px-4 py-3 sm:w-auto">
                    <img src="{{ asset('storage/madaia33/madaia33.png') }}"
                        alt="{{ config('app.name', 'Madaia') }} logo"
                        class="h-12 w-12 rounded-2xl object-cover shadow-lg shadow-amber-900/20" />
                    <div class="flex flex-col gap-1 text-left">
                        <span
                            class="text-sm font-semibold text-gray-800">{{ config('app.name', 'Komunitatea') }}</span>
                        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} ·
                            {{ __('general.nav.notices') }} · {{ __('general.nav.gallery') }}</p>
                    </div>
                </div>
                <nav class="grid gap-3 sm:grid-cols-2" aria-label="Footer">
                    <a href="{{ route('privacy-policy') }}"
                        class="hero-frame px-4 py-3 transition-colors hover:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ __('general.footer.privacy_policy') }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500">
                            {{ __('general.footer.privacy_policy_description') }}</p>
                    </a>
                    <a href="{{ route('legal-notice') }}"
                        class="hero-frame px-4 py-3 transition-colors hover:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ __('general.footer.legal_notice') }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500">
                            {{ __('general.footer.legal_notice_description') }}</p>
                    </a>
                </nav>
            </div>
        </div>
    </footer>

    @fluxScripts
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mobileMenu', () => ({
                open: false,

                toggleMenu() {
                    this.open = !this.open;

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
