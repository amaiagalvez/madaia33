@props([
    'href' => route(\App\SupportedLocales::routeName('home')),
    'showSubtitle' => true,
])

<a href="{{ $href }}"
    {{ $attributes->class('flex min-w-0 items-center gap-2 sm:gap-3 rounded-sm transition-colors hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2') }}>
    <img src="{{ asset('storage/madaia33/madaia33.png') }}"
        alt="{{ config('app.name', 'Madaia') }} logo"
        class="header-brand-mark h-10 w-10 sm:h-12 sm:w-12 rounded-2xl object-cover shadow-lg shadow-[#793d3d]/20 transition-all duration-200" />
    <span class="hidden sm:flex flex-col leading-none">
        <span class="text-lg font-semibold text-gray-900">{{ config('app.name', 'Madaia') }}</span>
        @if ($showSubtitle)
            <span
                class="header-brand-subtitle text-[11px] uppercase tracking-[0.18em] text-stone-500 transition-all duration-200">
                Labeaga 33, Urretxu
            </span>
        @endif
    </span>
</a>
