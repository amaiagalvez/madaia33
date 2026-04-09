<x-layouts::admin :title="__('admin.dashboard')">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ __('admin.dashboard') }}</h1>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('admin.stats.published_notices') }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $publishedNoticesCount }}</p>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('admin.stats.total_notices') }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalNoticesCount }}</p>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('admin.stats.images') }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $imagesCount }}</p>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('admin.stats.unread_messages') }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $unreadMessagesCount }}</p>
            </div>
        </div>
    </div>
</x-layouts::admin>
