@php
    $campaignLabel = $campaign->subject_eu ?: ($campaign->subject_es ?: ('#' . $campaign->id));
@endphp

<x-layouts::admin.main :title="$campaignLabel">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="$campaignLabel" />

        <div class="mb-5">
            <nav aria-label="{{ __('admin.campaigns') }}"
                class="inline-flex w-full items-center gap-2 rounded-xl border border-[#edd2c7]/70 bg-white/90 px-4 py-2.5 text-sm text-stone-600 shadow-xs"
                data-campaign-breadcrumb>
                <a href="{{ route('admin.campaigns') }}"
                    class="inline-flex items-center gap-2 font-medium text-[#793d3d] transition-colors hover:text-[#d9755b]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    {{ __('admin.campaigns') }}
                </a>
                <span class="text-stone-300">/</span>
                <span class="font-medium text-stone-900">{{ $campaignLabel }}</span>
            </nav>
        </div>

        <livewire:admin-campaign-detail :campaign="$campaign" />
    </div>
</x-layouts::admin.main>
