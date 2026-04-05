<x-layouts::public :title="__('general.nav.private')">
    @push('meta')
        <meta name="description" content="{{ __('general.nav.private') }} - {{ config('app.name') }}">
    @endpush

    <div
        class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 min-h-[60vh] flex items-center justify-center">
        <div class="w-full rounded-lg border border-gray-200 bg-white p-8 text-center shadow-sm">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                {{ __('general.nav.private') }}</h1>

            @auth
                <p class="text-base leading-relaxed text-gray-600 mb-6">
                    {{ __('general.private.auth_message') }}
                </p>
                <a href="{{ route('home') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700">
                    {{ __('general.buttons.back') }}
                </a>
            @else
                <p class="text-base leading-relaxed text-gray-600 mb-6">
                    {{ __('general.private.guest_message') }}
                </p>
                <a href="{{ route('login') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700">
                    {{ __('general.private.login_cta') }}
                </a>
            @endauth
        </div>
    </div>
</x-layouts::public>
