<x-layouts::shared.auth :title="__('admin.password_reset.confirm_title')">
    <x-auth.form-shell :title="__('admin.password_reset.confirm_title')" :description="__('admin.password_reset.confirm_description')">
        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6"
            x-data="{ showPassword: false }">
            @csrf

            <div>
                <label for="confirm-password-input"
                    class="mb-1 block text-sm font-medium text-gray-700">
                    {{ __('admin.password_reset.password') }}
                </label>
                <div class="relative">
                    <input id="confirm-password-input" name="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password" required
                        placeholder="{{ __('admin.password_reset.password') }}"
                        class="block w-full min-h-11 rounded-md border border-gray-300 bg-white py-2 pl-3 pr-12 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 inline-flex min-h-11 items-center justify-center px-3 text-gray-500 transition-colors hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-brand-600/40"
                        x-bind:aria-pressed="showPassword ? 'true' : 'false'"
                        aria-controls="confirm-password-input"
                        :aria-label="showPassword ? '{{ __('general.private.hide_password') }}' :
                            '{{ __('general.private.show_password') }}'"
                        :title="showPassword ? '{{ __('general.private.hide_password') }}' :
                            '{{ __('general.private.show_password') }}'">
                        <svg x-show="!showPassword" x-cloak class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.477 10.489A3 3 0 0 0 13.5 13.5" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.88 5.09A9.953 9.953 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.05 10.05 0 0 1-4.083 5.143M6.228 6.228a10.45 10.45 0 0 0-4.192 5.455c-.07.207-.07.431 0 .639C3.423 16.49 7.36 19.5 12 19.5c1.768 0 3.43-.438 4.885-1.21" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-brand inline-flex min-h-11 w-full justify-center"
                data-test="confirm-password-button">
                {{ __('admin.password_reset.confirm_submit') }}
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
