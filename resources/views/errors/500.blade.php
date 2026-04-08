<x-layouts::public :title="__('errors.500.title') ?? '500'">
    <div class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-prose w-full text-center">
            <p class="text-6xl font-bold text-gray-300 mb-4" aria-hidden="true">500</p>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                {{ __('errors.500.title') ?? 'Zerbitzariaren errorea / Error del servidor' }}
            </h1>
            <p class="text-base leading-relaxed text-gray-600 mb-8">
                {{ __('errors.500.message') ?? 'Zerbait gaizki joan da. Saiatu berriro geroago. / Algo salió mal. Inténtalo de nuevo más tarde.' }}
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route(\App\SupportedLocales::routeName('home')) }}"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700">
                    {{ __('general.buttons.back') }}
                </a>
                <a href="{{ route(\App\SupportedLocales::routeName('contact')) }}"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                    {{ __('general.nav.contact') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts::public>
