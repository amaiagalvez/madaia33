<x-layouts::public :title="__('errors.404.title') ?? '404'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <p class="text-6xl font-bold text-gray-300 mb-4" aria-hidden="true">404</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
            {{ __('errors.404.title') ?? 'Orrialdea ez da aurkitu / Página no encontrada' }}
        </h1>
        <p class="text-gray-600 mb-8">
            {{ __('errors.404.message') ?? 'Bilatzen ari zaren orrialdea ez da existitzen. / La página que buscas no existe.' }}
        </p>
        <a href="{{ route('home') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
            {{ __('general.buttons.back') }}
        </a>
    </div>
</x-layouts::public>
