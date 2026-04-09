<x-layouts::admin.main :title="__('admin.locations.detail_title')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.locations.portals') }}"
                class="text-sm text-stone-600 hover:text-stone-900">
                {{ __('admin.locations.back_to_list') }}
            </a>
        </div>

        <livewire:admin.location-detail :location="$location" />
    </div>
</x-layouts::admin.main>
