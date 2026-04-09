<x-layouts::public :title="__('contact.title')">
    @push('meta')
        <meta name="description" content="{{ __('contact.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="contact">
        <livewire:contact-form />
    </div>
</x-layouts::public>
