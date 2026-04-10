<x-layouts::admin.main :title="__('admin.owners.detail_title')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.owners.index') }}"
                class="text-sm text-stone-600 hover:text-stone-900">
                {{ __('admin.owners.back_to_list') }}
            </a>
        </div>

        <livewire:admin.owner-detail :owner="$owner" />
    </div>
</x-layouts::admin.main>
