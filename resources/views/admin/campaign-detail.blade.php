<x-layouts::admin.main :title="__('admin.campaigns')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('admin.campaigns')" :subtitle="$campaign->subject_eu ?? $campaign->subject_es ?? 'Campaign'" />

        <livewire:admin-campaign-detail :campaign="$campaign" />
    </div>
</x-layouts::admin.main>
