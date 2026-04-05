<x-layouts::public :title="__('general.nav.private')">
    @push('meta')
        <meta name="description" content="{{ __('general.private.seo_description') }}">
    @endpush

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14 min-h-[60vh] flex items-center justify-center"
        data-page="private">
        <div class="w-full rounded-2xl border border-gray-200 bg-linear-to-br from-white via-gray-50 to-gray-100 p-8 text-center shadow-sm"
            data-private-placeholder>
            <div class="mb-6 flex justify-center">
                <div class="page-icon-rose h-16 w-16 rounded-2xl">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                {{ __('general.nav.private') }}</h1>

            @auth
                <p class="text-base leading-relaxed text-gray-600 mb-6">
                    {{ __('general.private.auth_message') }}
                </p>
                <a href="{{ route('home') }}" class="btn-brand inline-flex min-h-11">
                    {{ __('general.buttons.back') }}
                </a>
            @else
                <p class="text-base leading-relaxed text-gray-600 mb-6">
                    {{ __('general.private.guest_message') }}
                </p>
                <a href="{{ route('login') }}" class="btn-brand inline-flex min-h-11">
                    {{ __('general.private.login_cta') }}
                </a>
            @endauth
        </div>
    </div>
</x-layouts::public>
