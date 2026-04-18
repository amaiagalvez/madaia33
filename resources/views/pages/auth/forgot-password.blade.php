<x-layouts::shared.auth :title="__('admin.password_reset.request_title')" :description="__('admin.password_reset.request_description')">
    <x-auth.form-shell :title="__('admin.password_reset.request_title')" :description="__('admin.password_reset.request_description')">
        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <div>
                <label for="forgot-password-email"
                    class="mb-1 block text-sm font-medium text-gray-700">
                    {{ __('admin.password_reset.email') }}
                </label>
                <input id="forgot-password-email" name="email" type="email" required autofocus
                    value="{{ old('email') }}" placeholder="email@example.com" autocomplete="email"
                    class="block w-full min-h-11 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600" />
            </div>

            <button type="submit" class="btn-brand inline-flex min-h-11 w-full justify-center"
                data-test="email-password-reset-link-button">
                {{ __('admin.password_reset.request_submit') }}
            </button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-500">
            <span>{{ __('admin.password_reset.back_prefix') }}</span>
            <a href="{{ route(\App\SupportedLocales::routeName('private')) }}"
                class="text-sm font-medium text-[#793d3d] underline decoration-brand-600/50 underline-offset-4 transition-colors hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/40">
                {{ __('admin.password_reset.back_link') }}</a>
        </div>
    </x-auth.form-shell>
</x-layouts::shared.auth>
