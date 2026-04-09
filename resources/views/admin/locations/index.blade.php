<x-layouts::admin.main :title="__('Ubicaciones')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('Ubicaciones')" />

        <livewire:admin.locations :type="$type" />
    </div>
</x-layouts::admin.main>
