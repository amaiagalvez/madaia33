<x-layouts::admin.main :title="__('admin.locations.detail_title')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="$location->name" />

        @php
            $backRoute = match ($location->type) {
                'local' => route('admin.locations.locals'),
                'garage' => route('admin.locations.garages'),
                'storage' => route('admin.locations.storages'),
                default => route('admin.locations.portals'),
            };

            $backLabel = __('admin.locations.types.' . $location->type);
        @endphp

        <div class="mb-5">
            <nav aria-label="{{ __('admin.locations.breadcrumb') }}"
                class="inline-flex w-full items-center gap-2 rounded-xl border border-[#edd2c7]/70 bg-white/90 px-4 py-2.5 text-sm text-stone-600 shadow-xs">
                <a href="{{ $backRoute }}"
                    class="inline-flex items-center gap-2 font-medium text-[#793d3d] transition-colors hover:text-[#d9755b]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    {{ $backLabel }}
                </a>
                <span class="text-stone-300">/</span>
                <span class="font-medium text-stone-900">{{ $location->name }}</span>
            </nav>
        </div>

        <livewire:admin.location-detail :location="$location" />
    </div>
</x-layouts::admin.main>
