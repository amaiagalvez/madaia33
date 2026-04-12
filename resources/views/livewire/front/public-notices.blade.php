<div>
    <x-front.public-page-header hero="notices" :title="__('notices.title')" :subtitle="__('notices.subtitle')" compact>
        <x-slot:actions>
            <div class="flex flex-wrap items-center justify-start gap-2 lg:justify-end"
                data-notices-filter>
                <button type="button" wire:click="setLocationFilter('')" data-notices-filter-btn="all"
                    @class([
                        'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                        'border-[#d9755b] bg-[#d9755b] text-white' => $locationFilter === '',
                        'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                            $locationFilter !== '',
                    ])>
                    {{ __('notices.filter.all') }}
                </button>
                @foreach ($filterLocations as $location)
                    <button type="button" wire:click="setLocationFilter('{{ $location['code'] }}')"
                        data-notices-filter-btn="{{ $location['code'] }}"
                        @class([
                            'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                            'border-[#d9755b] bg-[#d9755b] text-white' =>
                                $locationFilter === $location['code'],
                            'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                                $locationFilter !== $location['code'],
                        ])>
                        {{ $location['label'] }}
                    </button>
                @endforeach
            </div>
        </x-slot:actions>
        </x-public-page-header>

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
                    <x-front.notice-card :notice="$notice" :featured="$loop->first"
                        wire:key="notice-card-{{ $notice->id }}" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8 flex justify-center">
                {{ $notices->links() }}
            </div>
        @endif
</div>
