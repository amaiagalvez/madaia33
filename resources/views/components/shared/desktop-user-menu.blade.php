@php($isImpersonating = session()->has('impersonator_user_id'))
@php($currentUser = auth()->user())
@php($profileRoute = route(\App\SupportedLocales::routeName('profile')))

<flux:dropdown class="relative z-[70]" position="bottom" align="end" {{ $attributes }}>
    <button type="button"
        class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-[#edd2c7]/45 hover:text-[#793d3d]"
        data-test="sidebar-menu-button">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <span class="max-w-32 truncate">{{ $currentUser?->name }}</span>
        <flux:icon.chevron-down class="size-4" />
    </button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar :name="$currentUser?->name" :initials="$currentUser?->initials()" />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ $currentUser?->name }}</flux:heading>
                <flux:text class="truncate">{{ $currentUser?->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="$profileRoute" icon="user-circle">
                {{ __('profile.title') }}
            </flux:menu.item>
            <form method="POST"
                action="{{ $isImpersonating ? route('admin.users.stop_impersonation') : route('logout') }}"
                class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer" data-test="logout-button">
                    {{ $isImpersonating ? __('admin.users.back_to_my_user') : __('admin.logout') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
