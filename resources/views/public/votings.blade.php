<x-layouts::front.main :title="__('votings.front.page_title')">
    @push('meta')
        <meta name="description" content="{{ __('votings.front.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="votings">
        <h1 class="sr-only">{{ __('votings.front.page_title') }}</h1>

        <livewire:public-votings />
    </div>
</x-layouts::front.main>
