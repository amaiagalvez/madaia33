<x-layouts::front.main :title="$construction->title">
    @push('meta')
        <meta name="description" content="{{ __('constructions.front.detail_seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="construction-show">
        <h1 class="sr-only">{{ $construction->title }}</h1>

        <section class="hero-frame overflow-hidden p-6 sm:p-8" data-page-hero>
            <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-start">
                <div class="space-y-4">
                    <span
                        class="inline-flex items-center rounded-full bg-[#edd2c7] px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#793d3d]">
                        {{ __('constructions.front.detail_title') }}
                    </span>
                    <div class="space-y-3">
                        <h2 class="text-3xl font-semibold tracking-tight text-stone-900 md:text-4xl"
                            data-construction-title>
                            {{ $construction->title }}
                        </h2>
                        @if (filled($construction->description))
                            <p class="max-w-3xl text-sm leading-7 text-stone-600 sm:text-base">
                                {{ $construction->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white/90 p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">
                        {{ __('constructions.front.timeline') }}
                    </h3>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="font-medium text-stone-700">
                                {{ __('constructions.front.from') }}</dt>
                            <dd class="mt-1 text-stone-900">
                                {{ $construction->starts_at->translatedFormat('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-stone-700">
                                {{ __('constructions.front.to') }}</dt>
                            <dd class="mt-1 text-stone-900">
                                {{ $construction->ends_at?->translatedFormat('d/m/Y') ?? __('constructions.front.ongoing') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.65fr)_minmax(22rem,1fr)]">
            <section class="space-y-5" data-construction-notices>
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-stone-900">
                        {{ __('constructions.front.notices_title') }}</h2>
                </div>

                @forelse ($notices as $notice)
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-lg font-semibold text-stone-900">{{ $notice->title }}
                            </h3>
                            @if ($notice->published_at)
                                <span
                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    {{ $notice->published_at->translatedFormat('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                        <div class="prose prose-stone mt-4 max-w-none text-sm leading-7">
                            {!! nl2br(e($notice->content)) !!}
                        </div>

                        @if ($notice->documents->isNotEmpty())
                            <div class="mt-5 rounded-2xl border border-gray-200 bg-stone-50 p-4">
                                <h4 class="text-sm font-semibold text-stone-900">
                                    {{ __('constructions.front.documents_title') }}</h4>
                                <ul class="mt-3 space-y-2">
                                    @foreach ($notice->documents as $document)
                                        <li>
                                            <a href="{{ route('notice-documents.download', $document->token) }}"
                                                class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#edd2c7] bg-white px-3 py-2 text-sm font-medium text-[#793d3d] transition-colors hover:border-brand-600 hover:text-brand-hover"
                                                data-document-download="{{ $document->id }}">
                                                <svg class="h-4 w-4 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M12 16.5V3.75m0 12.75 4.5-4.5m-4.5 4.5-4.5-4.5M3.75 20.25h16.5" />
                                                </svg>
                                                {{ $document->filename }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </article>
                @empty
                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                        <p class="text-sm text-gray-500">
                            {{ __('constructions.front.empty_notices') }}</p>
                    </div>
                @endforelse
            </section>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <livewire:public-construction-inquiry-form :construction-id="$construction->id" />
            </aside>
        </div>
    </div>
</x-layouts::front.main>
