<x-layouts::admin.main :title="__('admin.dashboard')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('admin.dashboard')" />

        @if (auth()->id() === 1)
            <div class="mb-6 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.artisan.clear') }}"
                    onsubmit="return confirm('Executar optimize:clear?')">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Optimize:clear
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.artisan.migrate_and_seed') }}"
                    onsubmit="return confirm('Executar migrate + db:seed? Esto modifica la base de datos.')">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 shadow-sm transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>
                        Migrate + Seed
                    </button>
                </form>

                @if (session('status'))
                    <p class="mt-3 text-sm font-medium text-green-700">
                        {{ session('status') }}
                    </p>
                @endif
            </div>
        @endif

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
