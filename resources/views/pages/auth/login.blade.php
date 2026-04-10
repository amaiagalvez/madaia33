<x-layouts::shared.auth :title="__('admin.login.title')">
    <x-auth.form-shell :title="__('admin.login.title')">
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input name="email" :label="__('admin.login.email')" :value="old('email')"
                type="email" required autofocus autocomplete="email"
                placeholder="email@example.com" />

            <!-- Password -->
            <div class="relative">
                <flux:input name="password" :label="__('admin.login.password')" type="password"
                    required autocomplete="current-password"
                    :placeholder="__('admin.login.password')" viewable />
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full"
                    data-test="login-button">
                    {{ __('admin.login.submit') }}
                </flux:button>
            </div>
        </form>
    </x-auth.form-shell>
</x-layouts::shared.auth>
