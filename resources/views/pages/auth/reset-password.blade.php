<x-layouts::shared.auth :title="__('admin.password_reset.reset_title')">
    <x-auth.form-shell :title="__('admin.password_reset.reset_title')" :description="__('admin.password_reset.reset_description')">
        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <flux:input name="email" value="{{ request('email') }}" :label="__('admin.password_reset.email')"
                type="email" required autocomplete="email" />

            <flux:input name="password" :label="__('admin.password_reset.password')" type="password" required
                autocomplete="new-password" :placeholder="__('admin.password_reset.password')" viewable />

            <flux:input name="password_confirmation" :label="__('admin.password_reset.password_confirmation')" type="password"
                required autocomplete="new-password" :placeholder="__('admin.password_reset.password_confirmation')"
                viewable />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full"
                    data-test="reset-password-button">
                    {{ __('admin.password_reset.reset_submit') }}
                </flux:button>
            </div>
        </form>
    </x-auth.form-shell>
</x-layouts::shared.auth>
