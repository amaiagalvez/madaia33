<x-layouts::public :title="__('contact.title')">
    @push('meta')
        <meta name="description" content="{{ __('contact.title') }} - {{ config('app.name') }}">
    @endpush

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('contact.title') }}</h1>

        <livewire:contact-form />
    </div>
</x-layouts::public>
