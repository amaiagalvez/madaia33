<x-layouts::public :title="__('contact.title')">
    @push('meta')
        <meta name="description" content="{{ __('contact.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="contact">
        <x-public-page-header hero="contact" :title="__('contact.title')">
        </x-public-page-header>

        <div class="hero-frame p-5 sm:p-6">
            <div class="max-w-3xl">
                <livewire:contact-form />
            </div>
        </div>
    </div>
</x-layouts::public>
