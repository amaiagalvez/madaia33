<x-layouts::admin.main :title="__('admin.dashboard')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('admin.dashboard')" />

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-6">
            <x-admin.stat-card :label="__('admin.stats.published_notices')" :value="$publishedNoticesCount" />
            <x-admin.stat-card :label="__('admin.stats.total_notices')" :value="$totalNoticesCount" />
            <x-admin.stat-card :label="__('admin.stats.images')" :value="$imagesCount" />
            <x-admin.stat-card :label="__('admin.stats.unread_messages')" :value="$unreadMessagesCount" />
            <x-admin.stat-card :label="__('admin.stats.users')" :value="$usersCount" />
            <x-admin.stat-card :label="__('admin.stats.owners')" :value="$ownersCount" />
            <x-admin.stat-card :label="__('admin.stats.locations')" :value="$locationsCount" />
            <x-admin.stat-card :label="__('admin.stats.votings')" :value="$votingsCount" />
        </div>
    </div>
</x-layouts::admin.main>
