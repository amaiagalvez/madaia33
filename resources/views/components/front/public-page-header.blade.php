@props([
    'hero' => 'page',
    'title' => '',
    'subtitle' => null,
    'summary' => null,
    'description' => null,
    'variant' => 'default',
    'showLegalChips' => false,
    'compact' => false,
])

@php
    $isLegal = $variant === 'legal';
    $hasIcon = isset($icon) && trim((string) $icon) !== '';
    $hasActions = isset($actions) && trim((string) $actions) !== '';

    $sectionClasses = $isLegal
        ? 'mb-6 rounded-2xl border border-gray-200 bg-linear-to-br from-white via-gray-50 to-gray-100 p-6 shadow-sm'
        : ($compact
            ? 'mb-6 section-shell overflow-hidden p-4 sm:p-5'
            : 'mb-8 section-shell overflow-hidden p-6 sm:p-8');

    $titleClasses = $compact
        ? 'text-2xl md:text-3xl font-bold tracking-tight text-gray-900'
        : 'text-3xl md:text-4xl font-bold tracking-tight text-gray-900';

    $subtitleClasses = $compact
        ? 'mt-1 max-w-2xl text-sm text-gray-600'
        : 'mt-1 max-w-2xl text-sm sm:text-base text-gray-600';
@endphp

<section class="{{ $sectionClasses }}" data-page-hero="{{ $hero }}">
    @if ($isLegal)
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                {{ $description }}
            </p>
        @endif
    @else
        <div class="{{ $hasActions ? 'flex flex-col gap-4' : '' }}">
            <div class="{{ $hasIcon ? 'flex items-start gap-4' : '' }}">
                @if ($hasIcon)
                    {{ $icon }}
                @endif

                <div>
                    <h1 class="{{ $titleClasses }}">
                        {{ $title }}
                    </h1>

                    @if ($subtitle)
                        <p class="{{ $subtitleClasses }}">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if ($summary)
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
                            {{ $summary }}
                        </p>
                    @endif
                </div>
            </div>

            @if ($hasActions)
                {{ $actions }}
            @endif
        </div>
    @endif
</section>
