@props([
    'notice' => null,
    'showImage' => true,
    'image' => null,
])

@if ($notice)
    <div
        class="flex flex-col bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
        {{-- Image Header --}}
        @if ($showImage)
            @if ($image)
                <div class="relative overflow-hidden bg-gray-100 aspect-video">
                    <img src="{{ Storage::url($image->path) }}" alt="{{ $image->alt_text }}"
                        class="w-full h-full object-cover" />
                </div>
            @else
                <div
                    class="relative overflow-hidden bg-gradient-to-br from-gray-200 to-gray-300 aspect-video flex items-center justify-center">
                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-12-7.5h12a2.25 2.25 0 0 1 2.25 2.25v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 19.5V5.25a2.25 2.25 0 0 1 2.25-2.25Z" />
                    </svg>
                </div>
            @endif
        @endif

        {{-- Content Container --}}
        <div class="flex-1 p-4 flex flex-col gap-2">
            {{-- Title --}}
            <h3 class="text-base md:text-lg font-bold text-gray-900 line-clamp-2">
                {{ $notice->title }}
            </h3>

            {{-- Excerpt --}}
            <p class="text-sm md:text-base leading-relaxed text-gray-600 line-clamp-3">
                {{ Str::limit($notice->content, 120, '...') }}
            </p>

            {{-- Location Badges --}}
            @if ($notice->locations && $notice->locations->count() > 0)
                <div class="flex flex-wrap gap-2 mt-auto pt-2">
                    @foreach ($notice->locations as $location)
                        <span
                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                            @if ($location->location_type === 'portal')
                                {{ __('notices.portal') }} {{ $location->location_code }}
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
    </div>
@endif
