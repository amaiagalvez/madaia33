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

<body class="min-h-screen bg-white flex flex-col">

    {{-- Header / Navigation --}}
    <header
        class="sticky top-0 z-50 bg-white border-b border-gray-200 [padding-top:env(safe-area-inset-top)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo / Site name (left) --}}
                <a href="{{ route('home') }}"
                    class="text-lg font-semibold text-gray-900 hover:text-gray-700 transition-colors flex-shrink-0">
                    {{ config('app.name', 'Komunitatea') }}
                </a>

                {{-- Desktop navigation (center, hidden on mobile/tablet) --}}
                <nav class="hidden md:flex items-center gap-8 flex-1 justify-center"
                    aria-label="{{ __('general.nav.main') ?? 'Nabigazio nagusia' }}">
                    <a href="{{ route('notices') }}"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors {{ request()->routeIs('notices') ? 'text-gray-900 underline underline-offset-4' : '' }}">
                        {{ __('general.nav.notices') }}
                    </a>
                    <a href="{{ route('gallery') }}"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors {{ request()->routeIs('gallery') ? 'text-gray-900 underline underline-offset-4' : '' }}">
                        {{ __('general.nav.gallery') }}
                    </a>
                    <a href="{{ route('contact') }}"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors {{ request()->routeIs('contact') ? 'text-gray-900 underline underline-offset-4' : '' }}">
                        {{ __('general.nav.contact') }}
                    </a>
                    <a href="{{ route('private') }}"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors {{ request()->routeIs('private') ? 'text-gray-900 underline underline-offset-4' : '' }}">
                        {{ __('general.nav.private') }}
                    </a>
                </nav>

                {{-- Right section: Language switcher + Mobile menu button --}}
                <div class="flex items-center gap-4 ml-auto">
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
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors min-h-11 {{ request()->routeIs('notices') ? 'bg-gray-100' : '' }}"
                    data-first-menu-item x-ref="firstMenuItem">
                    {{ __('general.nav.notices') }}
                </a>
                <a href="{{ route('gallery') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors min-h-11 {{ request()->routeIs('gallery') ? 'bg-gray-100' : '' }}">
                    {{ __('general.nav.gallery') }}
                </a>
                <a href="{{ route('contact') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors min-h-11 {{ request()->routeIs('contact') ? 'bg-gray-100' : '' }}">
                    {{ __('general.nav.contact') }}
                </a>
                <a href="{{ route('private') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors min-h-11 {{ request()->routeIs('private') ? 'bg-gray-100' : '' }}">
                    {{ __('general.nav.private') }}
                </a>
            </nav>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer
        class="bg-gray-50 border-t border-gray-200 mt-auto [padding-bottom:env(safe-area-inset-bottom)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-6">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Komunitatea') }}
                </p>
                <nav class="flex items-center gap-4 sm:gap-6" aria-label="Footer">
                    <a href="{{ route('privacy-policy') }}"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        {{ __('general.footer.privacy_policy') }}
                    </a>
                    <a href="{{ route('legal-notice') }}"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        {{ __('general.footer.legal_notice') }}
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
