<x-layouts::admin :title="__('admin.settings')">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ __('admin.settings') }}</h1>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <livewire:admin-settings />
        </div>
    </div>
</x-layouts::admin>
