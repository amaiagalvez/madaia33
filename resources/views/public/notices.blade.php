<x-layouts::public :title="__('notices.title')">
    @push('meta')
        <meta name="description" content="{{ __('notices.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" data-page="notices">
        <livewire:public-notices />
    </div>
</x-layouts::public>
