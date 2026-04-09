<x-layouts::admin.main :title="__('admin.locations.title')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('admin.locations.title')" />

        <livewire:admin.locations :type="$type" />
    </div>
</x-layouts::admin.main>
