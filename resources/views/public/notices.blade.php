<x-layouts::public :title="__('notices.title')">
    @push('meta')
        <meta name="description" content="{{ __('notices.title') }} - {{ config('app.name') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('notices.title') }}</h1>

        <livewire:public-notices />
    </div>
</x-layouts::public>
