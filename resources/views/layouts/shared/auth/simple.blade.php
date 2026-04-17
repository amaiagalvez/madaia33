@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.shared.head')
    @if (filled($description))
        <meta name="description" content="{{ $description }}">
    @endif
</head>

<body
    class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div
        class="mx-auto flex min-h-screen w-full max-w-3xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full rounded-2xl border border-gray-200 bg-linear-to-br from-white via-gray-50 to-gray-100 p-8 shadow-sm sm:p-10"
            data-auth-shell>
            <a href="{{ route(\App\SupportedLocales::routeName('home')) }}"
                class="mb-8 flex flex-col items-center gap-2 font-medium" wire:navigate>
                <img src="{{ $publicLogoUrl }}" alt="" aria-hidden="true"
                    class="h-14 w-14 rounded-2xl border border-gray-200 bg-white object-contain p-1 shadow-sm" />
                <span class="sr-only">{{ $publicSiteName ?? config('app.name', '-') }}</span>
            </a>

            <div class="mx-auto flex w-full max-w-lg flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
