@php($isImpersonating = session()->has('impersonator_user_id'))

<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="route(\App\SupportedLocales::routeName('profile'))" icon="user-circle">
                {{ __('profile.title') }}
            </flux:menu.item>
            <form method="POST" action="{{ $isImpersonating ? route('admin.users.stop_impersonation') : route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer" data-test="logout-button">
                    {{ $isImpersonating ? __('admin.users.back_to_my_user') : __('admin.logout') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
