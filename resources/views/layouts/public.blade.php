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
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo / Site name --}}
                <a href="{{ route('home') }}"
                    class="text-lg font-semibold text-gray-900 hover:text-gray-700 transition-colors">
                    {{ config('app.name', 'Komunitatea') }}
                </a>

                {{-- Desktop navigation --}}
                <nav class="hidden md:flex items-center gap-6"
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

                {{-- Language switcher --}}
                <div class="flex items-center gap-4">
                    <livewire:language-switcher />

                    {{-- Mobile menu button --}}
                    <button type="button"
                        class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                        aria-label="Menua ireki / Abrir menú" x-data @click="$dispatch('toggle-mobile-menu')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile navigation --}}
        <div class="md:hidden border-t border-gray-200 bg-white" x-data="{ open: false }"
            @toggle-mobile-menu.window="open = !open" x-show="open" x-transition>
            <nav class="px-4 py-3 flex flex-col gap-1"
                aria-label="{{ __('general.nav.main') ?? 'Nabigazio nagusia' }}">
                <a href="{{ route('notices') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('notices') ? 'bg-gray-100' : '' }}">
                    {{ __('general.nav.notices') }}
                </a>
                <a href="{{ route('gallery') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('gallery') ? 'bg-gray-100' : '' }}">
                    {{ __('general.nav.gallery') }}
                </a>
                <a href="{{ route('contact') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('contact') ? 'bg-gray-100' : '' }}">
                    {{ __('general.nav.contact') }}
                </a>
                <a href="{{ route('private') }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('private') ? 'bg-gray-100' : '' }}">
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
    <footer class="bg-gray-50 border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Komunitatea') }}
                </p>
                <nav class="flex items-center gap-4" aria-label="Footer">
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
</body>

</html>
