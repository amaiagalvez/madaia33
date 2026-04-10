<x-layouts::shared.auth :title="__('Reset password')">
    <x-auth.form-shell :title="__('Reset password')" :description="__('Please enter your new password below')">
        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input name="email" value="{{ request('email') }}" :label="__('Email')"
                type="email" required autocomplete="email" />

            <!-- Password -->
            <flux:input name="password" :label="__('Password')" type="password" required
                autocomplete="new-password" :placeholder="__('Password')" viewable />

            <!-- Confirm Password -->
            <flux:input name="password_confirmation" :label="__('Confirm password')" type="password"
                required autocomplete="new-password" :placeholder="__('Confirm password')"
                viewable />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full"
                    data-test="reset-password-button">
                    {{ __('Reset password') }}
                </flux:button>
            </div>
        </form>
    </x-auth.form-shell>
</x-layouts::shared.auth>
