<x-layouts::public :title="__('gallery.title')">
    @push('meta')
        <meta name="description" content="{{ __('gallery.title') }} - {{ config('app.name') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('gallery.title') }}</h1>

        <livewire:image-gallery />
    </div>
</x-layouts::public>
