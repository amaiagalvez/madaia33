@props(['title'])

<header class="mt-2 mb-5 sm:mt-3 sm:mb-6" data-admin-page-header>
    <div
        class="relative w-full overflow-hidden rounded-2xl border border-[#edd2c7]/70 bg-linear-to-r from-white via-[#edd2c7]/25 to-white px-5 py-3.5 sm:px-6 sm:py-4">
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 left-0 w-1 bg-linear-to-b from-[#d9755b] to-[#d9755b]">
        </div>
        <h1 class="pl-4 text-xl font-semibold tracking-tight text-stone-900 sm:text-2xl">
            {{ $title }}</h1>
    </div>
</header>
