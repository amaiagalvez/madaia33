<x-layouts::public :title="__('errors.500.title') ?? '500'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <p class="text-6xl font-bold text-gray-300 mb-4" aria-hidden="true">500</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
            {{ __('errors.500.title') ?? 'Zerbitzariaren errorea / Error del servidor' }}
        </h1>
        <p class="text-gray-600 mb-8">
            {{ __('errors.500.message') ?? 'Zerbait gaizki joan da. Saiatu berriro geroago. / Algo salió mal. Inténtalo de nuevo más tarde.' }}
        </p>
        <a href="{{ route('home') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
            {{ __('general.buttons.back') }}
        </a>
    </div>
</x-layouts::public>
