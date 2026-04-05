<x-layouts::public :title="__('home.title')">
    @push('meta')
        <meta name="description" content="{{ config('app.name') }}">
    @endpush

    <!-- Hero Slider -->
    <livewire:hero-slider />

    <!-- Latest Notices Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-8">
            <h2 class="text-lg md:text-2xl font-bold uppercase mb-2 text-gray-900">
                {{ __('home.latest_notices') }}
            </h2>
            <p class="text-gray-600">{{ __('home.latest_notices_subtitle') }}</p>
        </div>

        @php
            $notices = \App\Models\Notice::public()->latest()->limit(6)->get();
        @endphp

        @if ($notices->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8"
                data-latest-notices>
                @foreach ($notices as $notice)
                    <x-notice-card :notice="$notice" />
                @endforeach
            </div>

            @if (\App\Models\Notice::public()->count() > 6)
                <div class="text-center">
                    <a href="{{ route('notices') }}"
                        class="inline-flex items-center px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-colors">
                        {{ __('home.view_all') }}
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-gray-600">{{ __('home.no_notices') }}</p>
            </div>
        @endif
    </div>
</x-layouts::public>
