<x-layouts::shared.auth :title="__('admin.password_reset.reset_title')">
    <x-auth.form-shell :title="__('admin.password_reset.reset_title')" :description="__('admin.password_reset.reset_description')">
        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="token"
                value="{{ request()->route('token') ?? ($token ?? null) }}">

            <flux:input name="email" value="{{ request('email') ?: $email ?? '' }}"
                :label="__('admin.password_reset.email')" type="email" required autocomplete="email"
                class="rounded-md border border-gray-300 bg-white text-gray-900 shadow-sm" />

            <flux:input name="password" :label="__('admin.password_reset.password')" type="password"
                required autocomplete="new-password"
                :placeholder="__('admin.password_reset.password')" viewable
                class="rounded-md border border-gray-300 bg-white text-gray-900 shadow-sm" />

            <flux:input name="password_confirmation"
                :label="__('admin.password_reset.password_confirmation')" type="password" required
                autocomplete="new-password"
                :placeholder="__('admin.password_reset.password_confirmation')" viewable
                class="rounded-md border border-gray-300 bg-white text-gray-900 shadow-sm" />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary"
                    class="btn-brand inline-flex min-h-11 w-full justify-center"
                    data-test="reset-password-button">
                    {{ __('admin.password_reset.reset_submit') }}
                </flux:button>
            </div>
        </form>
    </x-auth.form-shell>
</x-layouts::shared.auth>
