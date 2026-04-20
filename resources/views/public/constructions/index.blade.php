<x-layouts::front.main :title="__('constructions.front.page_title')">
    @push('meta')
        <meta name="description" content="{{ __('constructions.front.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="constructions-index">
        <h1 class="sr-only">{{ __('constructions.front.page_title') }}</h1>

        <section class="hero-frame overflow-hidden p-6 sm:p-8" data-page-hero>
            <div class="max-w-3xl space-y-4">
                <span
                    class="inline-flex items-center rounded-full bg-[#edd2c7] px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#793d3d]">
                    {{ __('admin.constructions.menu') }}
                </span>
                <div class="space-y-3">
                    <h2 class="text-3xl font-semibold tracking-tight text-stone-900 md:text-4xl">
                        {{ __('constructions.front.title') }}
                    </h2>
                    <p class="max-w-2xl text-sm leading-7 text-stone-600 sm:text-base">
                        {{ __('constructions.front.subtitle') }}
                    </p>
                </div>
            </div>
        </section>

        @if ($constructions->isEmpty())
            <div class="mt-8 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                <p class="text-sm text-gray-500">{{ __('constructions.front.empty') }}</p>
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3" data-constructions-grid>
                @foreach ($constructions as $construction)
                    <article
                        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-transform hover:-translate-y-0.5">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-xl font-semibold tracking-tight text-stone-900">
                                {{ $construction->title }}
                            </h2>
                            <span
                                class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                {{ __('constructions.front.ongoing') }}
                            </span>
                        </div>
                        @if (filled($construction->description))
                            <p class="mt-3 line-clamp-4 text-sm leading-6 text-stone-600">
                                {{ $construction->description }}
                            </p>
                        @endif
                        <dl class="mt-5 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <div class="rounded-xl border border-gray-200 bg-stone-50 px-4 py-3">
                                <dt class="font-medium text-stone-700">
                                    {{ __('constructions.front.from') }}</dt>
                                <dd class="mt-1 text-stone-900">
                                    {{ \App\Support\LocalizedDateFormatter::date($construction->starts_at) }}
                                </dd>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-stone-50 px-4 py-3">
                                <dt class="font-medium text-stone-700">
                                    {{ __('constructions.front.to') }}</dt>
                                <dd class="mt-1 text-stone-900">
                                    {{ $construction->ends_at ? \App\Support\LocalizedDateFormatter::date($construction->ends_at) : '...' }}
                                </dd>
                            </div>
                        </dl>
                        <a href="{{ route(\App\SupportedLocales::routeName('constructions.show'), ['slug' => $construction->slug]) }}"
                            class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#793d3d] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#5f2f2f] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2"
                            data-construction-link="{{ $construction->slug }}">
                            {{ __('general.buttons.edit') }}
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::front.main>
