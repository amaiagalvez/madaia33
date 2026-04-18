<x-layouts::front.main :title="__('general.nav.private')">
    @push('meta')
        <meta name="description" content="{{ __('general.private.seo_description') }}">
    @endpush

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14 min-h-[60vh] flex items-center justify-center"
        data-page="private">
        <div class="w-full rounded-2xl border border-gray-200 bg-linear-to-br from-white via-gray-50 to-gray-100 p-8 text-center shadow-sm"
            data-private-placeholder>
            <div class="mx-auto max-w-lg text-left">
                <div class="mb-6 flex justify-center">
                    <div class="page-icon-rose h-14 w-14 rounded-2xl">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-center text-2xl font-bold tracking-tight text-gray-900 md:text-3xl">
                    {{ __('general.private.form_title') }}
                </h1>

                <p class="mt-3 text-center text-sm leading-relaxed text-gray-600">
                    {{ __('general.private.guest_message') }}
                </p>

                @if ($errors->has('email'))
                    <div id="private-login-error"
                        class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                        aria-live="polite" role="alert" data-private-login-error>
                        {{ $errors->first('email') }}
                    </div>
                @endif

                <form class="mt-7 space-y-4" action="{{ route('login.store') }}" method="POST"
                    x-data="{ showPassword: false }" novalidate>
                    @csrf
                    <div>
                        <label for="private-username"
                            class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('general.private.username') }}
                        </label>
                        <input id="private-username" name="email" type="text"
                            autocomplete="username" required
                            class="block w-full min-h-11 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600"
                            value="{{ old('email') }}"
                            @if ($errors->has('email')) aria-describedby="private-login-error" aria-invalid="true" @endif
                            placeholder="{{ __('general.private.username_placeholder') }}" />
                    </div>

                    <div>
                        <label for="private-password"
                            class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('general.private.password') }}
                        </label>
                        <div class="relative">
                            <input id="private-password" name="password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password" required
                                class="block w-full min-h-11 rounded-md border border-gray-300 bg-white py-2 pl-3 pr-12 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 inline-flex min-h-11 items-center justify-center px-3 text-gray-500 transition-colors hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-brand-600/40"
                                x-bind:aria-pressed="showPassword ? 'true' : 'false'"
                                aria-controls="private-password"
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
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m3 3 18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.477 10.489A3 3 0 0 0 13.5 13.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.88 5.09A9.953 9.953 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.05 10.05 0 0 1-4.083 5.143M6.228 6.228a10.45 10.45 0 0 0-4.192 5.455c-.07.207-.07.431 0 .639C3.423 16.49 7.36 19.5 12 19.5c1.768 0 3.43-.438 4.885-1.21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="btn-brand inline-flex min-h-11 w-full justify-center"
                        data-test="login-button">
                        {{ __('general.private.login_cta') }}
                    </button>

                    @if (\Illuminate\Support\Facades\Route::has('password.request'))
                        <div class="pt-1 text-center">
                            <a href="{{ route(\App\SupportedLocales::routeName('password.request')) }}"
                                class="text-sm font-medium text-[#793d3d] underline decoration-brand-600/50 underline-offset-4 transition-colors hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/40">
                                {{ __('general.private.change_password_option') }}
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-layouts::front.main>
