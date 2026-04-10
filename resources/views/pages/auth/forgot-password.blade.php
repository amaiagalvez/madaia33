<x-layouts::shared.auth :title="__('admin.password_reset.request_title')">
    <x-auth.form-shell :title="__('admin.password_reset.request_title')" :description="__('admin.password_reset.request_description')">
        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="email" :label="__('admin.password_reset.email')" type="email"
                required autofocus :value="old('email')" placeholder="email@example.com"
                class="rounded-md border border-gray-300 bg-white text-gray-900 shadow-sm" />

            <flux:button variant="primary" type="submit"
                class="btn-brand inline-flex min-h-11 w-full justify-center"
                data-test="email-password-reset-link-button">
                {{ __('admin.password_reset.request_submit') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-500">
            <span>{{ __('admin.password_reset.back_prefix') }}</span>
            <flux:link :href="route(\App\SupportedLocales::routeName('private'))"
                class="font-medium text-[#793d3d] underline decoration-[#d9755b]/50 underline-offset-4 transition-colors hover:text-[#d9755b]"
                wire:navigate>
                {{ __('admin.password_reset.back_link') }}</flux:link>
        </div>
    </x-auth.form-shell>
</x-layouts::shared.auth>
