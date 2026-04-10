<x-layouts::shared.auth :title="__('Forgot password')">
    <x-auth.form-shell :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')">
        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input name="email" :label="__('Email address')" type="email" required autofocus
                placeholder="email@example.com" />

            <flux:button variant="primary" type="submit" class="w-full"
                data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </x-auth.form-shell>
</x-layouts::shared.auth>
