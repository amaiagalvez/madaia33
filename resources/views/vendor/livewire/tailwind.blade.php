@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
            <div class="flex items-center justify-between gap-3 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm font-semibold text-gray-400 cursor-not-allowed"
                            aria-disabled="true">
                            {{ __('general.pagination.previous') }}
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition-colors hover:border-[#d9755b] hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b]/40">
                            {{ __('general.pagination.previous') }}
                        </button>
                    @endif
                </span>

                <span
                    class="rounded-xl border border-[#d9755b]/25 bg-[#edd2c7]/45 px-3 py-2 text-xs font-semibold text-[#793d3d]">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled"
                            dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition-colors hover:border-[#d9755b] hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b]/40">
                            {{ __('general.pagination.next') }}
                        </button>
                    @else
                        <span
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm font-semibold text-gray-400 cursor-not-allowed"
                            aria-disabled="true">
                            {{ __('general.pagination.next') }}
                        </span>
                    @endif
                </span>
            </div>

            <div class="hidden sm:flex sm:flex-col sm:items-center sm:gap-3">
                <p
                    class="rounded-full border border-[#d9755b]/20 bg-[#edd2c7]/35 px-4 py-1.5 text-xs font-semibold text-[#793d3d]">
                    {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} /
                    {{ $paginator->total() }}
                </p>

                <div
                    class="inline-flex flex-wrap items-center justify-center gap-1 rounded-2xl border border-gray-200 bg-white p-1.5 shadow-sm shadow-[#793d3d]/5">
                    @if ($paginator->onFirstPage())
                        <span
                            class="inline-flex min-h-10 items-center justify-center gap-1 rounded-xl px-3 text-sm font-semibold text-gray-300"
                            aria-disabled="true" aria-label="{{ __('general.pagination.previous') }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                            <span>{{ __('general.pagination.previous') }}</span>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                            class="inline-flex min-h-10 items-center justify-center gap-1 rounded-xl px-3 text-sm font-semibold text-gray-600 transition-colors hover:bg-[#edd2c7]/60 hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b]/40"
                            aria-label="{{ __('general.pagination.previous') }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                            <span>{{ __('general.pagination.previous') }}</span>
                        </button>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-1 text-sm font-semibold text-gray-400" aria-disabled="true">
                                {{ $element }}
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                    @if ($page === $paginator->currentPage())
                                        <span aria-current="page"
                                            class="inline-flex min-w-10 items-center justify-center rounded-xl bg-[#d9755b] px-3 py-2 text-sm font-semibold text-white shadow-sm">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <button type="button"
                                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            class="inline-flex min-w-10 items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold text-gray-600 transition-colors hover:bg-[#edd2c7]/60 hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b]/40"
                                            aria-label="{{ __('general.pagination.go_to_page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                            class="inline-flex min-h-10 items-center justify-center gap-1 rounded-xl px-3 text-sm font-semibold text-gray-600 transition-colors hover:bg-[#edd2c7]/60 hover:text-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b]/40"
                            aria-label="{{ __('general.pagination.next') }}">
                            <span>{{ __('general.pagination.next') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    @else
                        <span
                            class="inline-flex min-h-10 items-center justify-center gap-1 rounded-xl px-3 text-sm font-semibold text-gray-300"
                            aria-disabled="true" aria-label="{{ __('general.pagination.next') }}">
                            <span>{{ __('general.pagination.next') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </nav>
    @endif
</div>
