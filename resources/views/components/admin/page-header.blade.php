@props(['title'])

<header class="mb-10 sm:mb-12" data-admin-page-header>
    <div
        class="relative overflow-hidden rounded-2xl border border-[#edd2c7]/70 bg-linear-to-r from-white via-[#edd2c7]/25 to-white px-6 py-5 sm:px-7 sm:py-6">
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 left-0 w-1 bg-linear-to-b from-[#d9755b] to-[#d9755b]">
        </div>
        <h1 class="pl-4 text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">
            {{ $title }}</h1>
    </div>
</header>
