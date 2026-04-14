<x-layouts::admin.main :title="__('campaigns.admin.invalid_contacts')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('campaigns.admin.invalid_contacts')" />

        <livewire:admin-invalid-contacts-list />
    </div>
</x-layouts::admin.main>
