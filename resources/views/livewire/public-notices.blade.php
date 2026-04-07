<div>
    <section class="mb-8 section-shell overflow-hidden p-6 sm:p-8" data-page-hero="notices">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                    {{ __('notices.title') }}
                </h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-600 sm:text-base">
                    {{ __('notices.subtitle') }}
                </p>
            </div>

            <div class="w-full lg:w-auto" data-notices-filter>
                <label for="location-filter" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ __('notices.filter.label') }}
                </label>
                <p class="mb-3 text-xs text-gray-500 sm:text-sm">{{ __('notices.filter_hint') }}</p>
                <div class="glass-panel p-3 sm:p-4">
                    <select id="location-filter" wire:model.live="locationFilter"
                        aria-controls="notices-list"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:w-64">
                        <option value="">{{ __('notices.filter.all') }}</option>
                        <optgroup label="{{ __('notices.portal') }}">
                            @foreach (\App\CommunityLocations::PORTALS as $portal)
                                <option value="{{ $portal }}">{{ __('notices.portal') }}
                                    {{ $portal }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="{{ __('notices.garage') }}">
                            @foreach (\App\CommunityLocations::GARAGES as $floor)
                                <option value="{{ $floor }}">{{ __('notices.garage') }}
                                    {{ $floor }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
    </section>

    {{-- Notices list --}}
    {{-- Notices grid --}}
    @if ($notices->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center"
            data-notices-empty>
            <div
                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
            </div>
            <p class="text-gray-500 text-sm">{{ __('notices.empty') }}</p>
        </div>
    @else
        @php
            $featuredNotice = $notices->first();
        @endphp

        <div id="notices-list"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8"
            data-notices-grid>
            @foreach ($notices as $notice)
                <x-notice-card :notice="$notice" :featured="$loop->first"
                    wire:key="notice-card-{{ $notice->id }}" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $notices->links() }}
        </div>
    @endif
</div>
