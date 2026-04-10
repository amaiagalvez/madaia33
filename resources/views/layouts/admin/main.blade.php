<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>
        {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-100">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside
            class="hidden lg:flex lg:flex-col w-64 bg-stone-50 border-r border-stone-200 fixed inset-y-0 left-0 z-40">
            {{-- Sidebar header --}}
            <div class="flex items-center h-16 px-6 border-b border-stone-200">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 text-base font-semibold text-stone-900 hover:text-stone-700 transition-colors">
                    <img src="{{ asset('storage/madaia33/madaia33.webp') }}"
                        alt="{{ config('app.name', 'Laravel') }} logo"
                        class="h-8 w-8 rounded-xl object-cover" />
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>

            {{-- Sidebar navigation --}}
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto"
                aria-label="{{ __('admin.dashboard') }}">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    {{ __('admin.dashboard') }}
                </a>

                <p class="px-3 pt-4 text-xs font-semibold uppercase tracking-wide text-stone-400">
                    {{ __('admin.sidebar.web') }}
                </p>

                @if (auth()->user()?->canManageNotices())
                    <a href="{{ route('admin.notices') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.notices') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                        </svg>
                        {{ __('admin.notices') }}
                    </a>
                @endif

                @if (auth()->user()?->isSuperadmin())
                    <a href="{{ route('admin.images') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.images') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        {{ __('admin.gallery') }}
                    </a>

                    <a href="{{ route('admin.messages') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.messages') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        {{ __('admin.messages') }}
                    </a>
                @endif

                <p class="px-3 pt-4 text-xs font-semibold uppercase tracking-wide text-stone-400">
                    {{ __('admin.sidebar.community') }}
                </p>

                @if (auth()->user()
                        ?->hasAnyRole(['superadmin', 'admin_general', 'admin_comunidad']))
                    <a href="{{ route('admin.locations.portals') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.locations.*') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 21v-4.5m0 0a2.25 2.25 0 0 1 4.5 0m-4.5 0h4.5M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-9Z" />
                        </svg>
                        {{ __('admin.locations.menu') }}
                    </a>
                @endif

                @if (auth()->user()?->isSuperadmin())
                    <a href="{{ route('admin.owners.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.owners.*') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a8.964 8.964 0 0 0 3-6.72A9 9 0 1 0 3 12a8.964 8.964 0 0 0 3 6.72m12 0a8.966 8.966 0 0 1-12 0m12 0A10.953 10.953 0 0 1 12 21c-2.331 0-4.496-.727-6-1.968m12 0a5.95 5.95 0 0 0-12 0m12 0a5.952 5.952 0 0 1-12 0m6-10.5a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z" />
                        </svg>
                        {{ __('admin.owners.menu') }}
                    </a>
                @endif

                @if (auth()->user()?->canManageUsers())
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a8.964 8.964 0 0 0 3-6.72A9 9 0 1 0 3 12a8.964 8.964 0 0 0 3 6.72m12 0a8.966 8.966 0 0 1-12 0m12 0A10.953 10.953 0 0 1 12 21c-2.331 0-4.496-.727-6-1.968m12 0a5.95 5.95 0 0 0-12 0m12 0a5.952 5.952 0 0 1-12 0m6-10.5a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z" />
                        </svg>
                        {{ __('admin.users.menu') }}
                    </a>
                @endif

                @if (auth()->user()?->isSuperadmin())
                    <a href="{{ route('admin.votings') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.votings') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
                        </svg>
                        {{ __('admin.votings.menu') }}
                    </a>
                @endif

                <p class="px-3 pt-4 text-xs font-semibold uppercase tracking-wide text-stone-400">
                    {{ __('admin.sidebar.configuration') }}
                </p>

                @if (auth()->user()?->isSuperadmin())
                    <a href="{{ route('admin.settings') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.settings') ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        {{ __('admin.settings') }}
                    </a>
                @endif
            </nav>

        </aside>

        {{-- Mobile header --}}
        <div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-stone-50 border-b border-stone-200 h-16 flex items-center px-4 gap-4"
            x-data="{ open: false }">
            <button type="button"
                class="p-2 rounded-md text-stone-600 hover:text-[#793d3d] hover:bg-[#edd2c7]/45 transition-colors"
                aria-label="Menua ireki / Abrir menú" @click="open = !open">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('storage/madaia33/madaia33.webp') }}"
                    alt="{{ config('app.name', 'Madaia') }} logo"
                    class="h-8 w-8 rounded-xl object-cover" />
                <span
                    class="text-base font-semibold text-stone-900">{{ __('admin.dashboard') }}</span>
            </div>

            {{-- Mobile sidebar overlay --}}
            <div x-show="open" x-transition class="fixed inset-0 z-50 flex">
                <div class="fixed inset-0 bg-black/30" @click="open = false" aria-hidden="true">
                </div>
                <div
                    class="relative flex flex-col w-64 bg-stone-50 border-r border-stone-200 h-full overflow-y-auto">
                    <div
                        class="flex items-center justify-between h-16 px-6 border-b border-stone-200">
                        <span
                            class="flex items-center gap-2 text-base font-semibold text-gray-900">
                            <img src="{{ asset('storage/madaia33/madaia33.webp') }}"
                                alt="{{ config('app.name', 'Madaia') }} logo"
                                class="h-8 w-8 rounded-xl object-cover" />
                            {{ __('admin.dashboard') }}
                        </span>
                        <button type="button" @click="open = false"
                            class="p-1 rounded text-gray-500 hover:text-gray-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <nav class="flex-1 px-4 py-6 space-y-1"
                        aria-label="{{ __('admin.dashboard') }}">
                        <a href="{{ route('admin.dashboard') }}"
                            class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.dashboard') }}</a>
                        <p
                            class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-stone-400">
                            {{ __('admin.sidebar.web') }}
                        </p>
                        @if (auth()->user()?->canManageNotices())
                            <a href="{{ route('admin.notices') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.notices') }}</a>
                        @endif
                        @if (auth()->user()?->isSuperadmin())
                            <a href="{{ route('admin.images') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.gallery') }}</a>
                            <a href="{{ route('admin.messages') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.messages') }}</a>
                        @endif
                        <p
                            class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-stone-400">
                            {{ __('admin.sidebar.community') }}
                        </p>
                        @if (auth()->user()
                                ?->hasAnyRole(['superadmin', 'admin_general', 'admin_comunidad']))
                            <a href="{{ route('admin.locations.portals') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.locations.menu') }}</a>
                        @endif
                        @if (auth()->user()?->isSuperadmin())
                            <a href="{{ route('admin.owners.index') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.owners.menu') }}</a>
                        @endif
                        @if (auth()->user()?->canManageUsers())
                            <a href="{{ route('admin.users.index') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.users.menu') }}</a>
                        @endif
                        @if (auth()->user()?->isSuperadmin())
                            <a href="{{ route('admin.votings') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.votings.menu') }}</a>
                        @endif
                        <p
                            class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-stone-400">
                            {{ __('admin.sidebar.configuration') }}
                        </p>
                        @if (auth()->user()?->isSuperadmin())
                            <a href="{{ route('admin.settings') }}"
                                class="block px-3 py-2 rounded-md text-sm font-medium text-stone-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">{{ __('admin.settings') }}</a>
                        @endif
                    </nav>
                    <div class="border-t border-stone-200 p-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-3 py-2 rounded-md text-sm font-medium text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d] transition-colors">
                                {{ __('admin.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main content area --}}
        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
            <header
                class="hidden lg:flex h-16 items-center justify-end border-b border-stone-200 bg-stone-50 px-6">
                <nav class="flex items-center gap-4" aria-label="{{ __('admin.dashboard') }}">
                    <div class="text-right min-w-0">
                        <p class="text-sm font-medium text-stone-900 truncate">
                            {{ auth()->user()?->name }}</p>
                        <p class="text-xs text-stone-500 truncate">{{ auth()->user()?->email }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-md border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-600 transition-colors hover:bg-[#edd2c7]/45 hover:text-[#793d3d]">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                            {{ __('admin.logout') }}
                        </button>
                    </form>
                </nav>
            </header>

            <main class="flex-1 pt-16 lg:pt-0 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

</body>

</html>
