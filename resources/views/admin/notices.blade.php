<x-layouts::admin :title="__('admin.notices')">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ __('admin.notices') }}</h1>

        <livewire:admin-notice-manager />
    </div>
</x-layouts::admin>
