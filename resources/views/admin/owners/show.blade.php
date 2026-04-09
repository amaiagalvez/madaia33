<x-layouts::admin.main :title="__('Detalle de propietaria')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.owners.index') }}"
                class="text-sm text-stone-600 hover:text-stone-900">
                {{ __('Volver a propietarias') }}
            </a>
        </div>

        <livewire:admin.owner-detail :owner="$owner" />
    </div>
</x-layouts::admin.main>
