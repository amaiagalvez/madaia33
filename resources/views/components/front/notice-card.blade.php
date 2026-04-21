@props([
    'notice' => null,
    'showImage' => true,
    'image' => null,
    'featured' => false,
])

@if ($notice)
    <article
        class="elevated-card group flex flex-col overflow-hidden {{ $featured ? 'sm:col-span-2 lg:col-span-3 lg:grid lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]' : '' }}">
        {{-- Image Header --}}
        @if ($showImage)
            @if ($image)
                @php
                    $imageUrl =
                        data_get($image, 'public_url') ??
                        (data_get($image, 'path') ?? asset('favicon.svg'));
                    $fallbackTitle = trim(strip_tags((string) $notice->title));
                    $rawImageAlt = data_get($image, 'alt_text') ?: $fallbackTitle;
                    $altText = trim(
                        (string) preg_replace(
                            '/\b(image|imagen|irudia)\b/iu',
                            '',
                            strip_tags((string) $rawImageAlt),
                        ),
                    );
                    $altText = $altText !== '' ? $altText : $fallbackTitle;
                @endphp
                <div
                    class="relative overflow-hidden bg-gray-100 aspect-video {{ $featured ? 'lg:aspect-auto lg:min-h-80' : '' }}">
                    <img src="{{ $imageUrl }}" alt="{{ $altText }}"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        loading="lazy" decoding="async" />
                    <div
                        class="absolute inset-x-0 bottom-0 h-24 bg-linear-to-t from-slate-950/35 to-transparent">
                    </div>
                </div>
            @else
                <div
                    class="relative flex min-h-20 items-center justify-center overflow-hidden bg-linear-to-br from-[#edd2c7]/20 via-gray-100 to-gray-200 {{ $featured ? 'lg:min-h-56' : 'sm:min-h-24' }}">
                    <svg class="h-8 w-8 text-brand-600/30" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
            @endif
        @endif

        {{-- Content Container --}}
        <div class="flex flex-1 flex-col gap-2 p-4 {{ $featured ? 'lg:p-6' : '' }}">
            {{-- Date --}}
            @if ($notice->published_at)
                <div class="flex items-center justify-between gap-3">
                    <time datetime="{{ $notice->published_at->toIso8601String() }}"
                        data-notice-published-at
                        class="inline-flex items-center rounded-full bg-brand-600/20 px-2.5 py-1 text-xs font-semibold text-[#793d3d]">
                        {{ \App\Support\LocalizedDateFormatter::date($notice->published_at) }}
                    </time>
                </div>
            @endif

            {{-- Title --}}
            <div class="font-bold text-gray-900 line-clamp-2 [&_p]:m-0 {{ $featured ? 'text-xl md:text-2xl lg:text-3xl' : 'text-base md:text-lg' }}"
                data-notice-title>
                {!! $notice->title !!}
            </div>

            {{-- Excerpt --}}
            <div class="leading-relaxed text-gray-600 [&_p]:m-0 {{ $featured ? 'text-sm md:text-base lg:text-lg' : 'text-sm md:text-base' }}"
                data-notice-content>
                {!! $notice->content !!}
            </div>

            {{-- Location Badges --}}
            @if ($notice->locations && $notice->locations->count() > 0)
                <div class="mt-auto flex flex-wrap gap-1.5 pt-2">
                    @foreach ($notice->locations as $location)
                        <span
                            class="inline-flex items-center rounded-full border border-brand-600/20 bg-[#edd2c7]/30 px-2.5 py-0.5 text-xs font-medium text-[#793d3d]">
                            @if ($location->location_type === 'portal')
                                {{ __('notices.portal') }} {{ $location->location_code }}
                            @elseif ($location->location_type === 'local')
                                {{ __('notices.local') }} {{ $location->location_code }}
                            @elseif ($location->location_type === 'garage')
                                {{ __('notices.garage') }} {{ $location->location_code }}
                            @else
                                {{ ucfirst($location->location_type) }}
                                {{ $location->location_code }}
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </article>
@endif
