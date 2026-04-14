<x-layouts::admin.main :title="__('admin.campaigns')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('admin.campaigns')" />

        <livewire:admin-campaign-manager />
    </div>
</x-layouts::admin.main>
