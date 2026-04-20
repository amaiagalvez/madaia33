<x-layouts::front.main :title="$construction->title">
    @push('meta')
        <meta name="description" content="{{ __('constructions.front.detail_seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="construction-show"
        x-data="{ inquiryOpen: false, inquiryStatusType: null, inquiryStatusMessage: '' }" @keydown.escape.window="inquiryOpen = false"
        @construction-inquiry-submitted.window="
            if ($event.detail?.statusType === 'success') {
                inquiryOpen = false;
                inquiryStatusType = 'success';
                inquiryStatusMessage = $event.detail?.statusMessage ?? '';
            }
        ">
        <h1 class="sr-only">{{ $construction->title }}</h1>

        <header data-construction-header-card
            class="mb-8 rounded-2xl border border-brand-600/25 bg-linear-to-r from-[#edd2c7]/35 via-white to-[#f1bd4d]/15 p-4 sm:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                        {{ __('constructions.front.detail_title') }}</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-900"
                        data-construction-title>
                        {{ $construction->title }}</h2>
                    @if (filled($construction->description))
                        <p
                            class="mt-2 max-w-3xl wrap-break-word text-sm leading-relaxed text-gray-600">
                            {{ $construction->description }}
                        </p>
                    @endif
                </div>
                <div class="w-full md:w-auto md:max-w-xs">
                    <p class="mb-2 max-w-full wrap-break-word text-xs leading-relaxed text-gray-600"
                        data-construction-contact-helper-text>
                        {{ __('constructions.front.contact_helper') }}
                    </p>
                    <button type="button" @click="inquiryOpen = true"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-brand-600 hover:text-[#793d3d] sm:w-auto"
                        data-construction-contact-trigger>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        {{ __('constructions.inquiry.send') }}
                    </button>

                    <div x-cloak x-show="inquiryStatusType === 'success'"
                        class="mt-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                        role="alert" aria-live="polite" data-construction-inquiry-success>
                        <span x-text="inquiryStatusMessage"></span>
                    </div>
                </div>
            </div>
        </header>

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
                                                <path stroke-linecap="round" stroke-linejoin="round"
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
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                    <p class="text-sm text-gray-500">
                        {{ __('constructions.front.empty_notices') }}</p>
                </div>
            @endforelse
        </section>

        <template x-teleport="body">
            <div x-cloak x-show="inquiryOpen" x-transition.opacity
                class="fixed inset-0 z-120 overflow-y-auto p-4 sm:p-6" aria-modal="true"
                role="dialog" aria-labelledby="construction-inquiry-modal-title"
                @keydown.escape.window="inquiryOpen = false" data-construction-inquiry-modal>
                <div class="fixed inset-0 bg-black/50" @click="inquiryOpen = false"
                    aria-hidden="true"></div>

                <div class="relative z-10 flex min-h-full items-center justify-center">
                    <div
                        class="relative w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h2 id="construction-inquiry-modal-title"
                                    class="text-base font-semibold text-gray-900">
                                    {{ __('constructions.inquiry.title') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('constructions.inquiry.subtitle') }}
                                </p>
                            </div>
                            <button type="button" @click="inquiryOpen = false"
                                class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                aria-label="{{ __('general.close') }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div data-construction-inquiry-form-modal>
                            <livewire:public-construction-inquiry-form :construction-id="$construction->id"
                                :key="'construction-inquiry-' . $construction->id" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    </div>
</x-layouts::front.main>
