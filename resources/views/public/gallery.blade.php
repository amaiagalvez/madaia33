<x-layouts::public :title="__('gallery.title')">
    @push('meta')
        <meta name="description" content="{{ __('gallery.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="gallery">
        <x-public-page-header hero="gallery" :title="__('gallery.title')" :subtitle="__('gallery.subtitle')" />

        <livewire:image-gallery />
    </div>
</x-layouts::public>
