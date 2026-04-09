<x-layouts::admin :title="__('admin.messages')">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ __('admin.messages') }}</h1>

        <livewire:admin-message-inbox />
    </div>
</x-layouts::admin>
