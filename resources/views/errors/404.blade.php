<x-layouts::front.main :title="__('errors.404.title')">
    <div class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-prose w-full text-center">
            <p class="text-6xl font-bold text-gray-300 mb-4" aria-hidden="true">404</p>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                {{ __('errors.404.title') }}
            </h1>
            <p class="text-base leading-relaxed text-gray-600 mb-8">
                {{ __('errors.404.message') }}
            </p>
            <a href="{{ route(\App\SupportedLocales::routeName('home')) }}"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700">
                {{ __('general.buttons.back') }}
            </a>
        </div>
    </div>
</x-layouts::front.main>
