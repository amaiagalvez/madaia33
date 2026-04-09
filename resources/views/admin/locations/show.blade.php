<x-layouts::admin.main :title="__('Detalle de ubicación')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.locations.portals') }}"
                class="text-sm text-stone-600 hover:text-stone-900">
                {{ __('Volver a ubicaciones') }}
            </a>
        </div>

        <livewire:admin.location-detail :location="$location" />
    </div>
</x-layouts::admin.main>
