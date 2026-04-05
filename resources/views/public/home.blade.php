<x-layouts::public :title="__('general.nav.notices')">
    @push('meta')
        <meta name="description" content="{{ config('app.name') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ config('app.name') }}</h1>

        <p class="text-gray-600">{{ __('general.nav.notices') }}</p>
    </div>
</x-layouts::public>
