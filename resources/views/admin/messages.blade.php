<x-layouts::admin.main :title="__('admin.messages')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('admin.messages')" />

        <livewire:admin-message-inbox />
    </div>
</x-layouts::admin.main>
