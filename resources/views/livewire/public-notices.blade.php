<div>
    {{-- Filter by location --}}
    <div class="mb-6">
        <label for="location-filter" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('notices.filter.label') }}
        </label>
        <select id="location-filter" wire:model.live="locationFilter"
            class="block w-full sm:w-64 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500">
            <option value="">{{ __('notices.filter.all') }}</option>
            <optgroup label="{{ __('notices.portal') }}">
                @foreach (\App\CommunityLocations::PORTALS as $portal)
                    <option value="{{ $portal }}">{{ __('notices.portal') }} {{ $portal }}</option>
                @endforeach
            </optgroup>
            <optgroup label="{{ __('notices.garage') }}">
                @foreach (\App\CommunityLocations::GARAGES as $floor)
                    <option value="{{ $floor }}">{{ __('notices.garage') }} {{ $floor }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>

    {{-- Notices list --}}
    @if ($notices->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
            <p class="text-gray-500 text-sm">{{ __('notices.empty') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($notices as $notice)
                <article class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                        <h2 class="text-lg font-semibold text-gray-900 leading-snug">
                            {{ $notice->title }}
                        </h2>

                        {{-- Fallback indicator --}}
                        @if (!$this->hasTranslation($notice))
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800"
                                title="{{ __('notices.no_translation') }}">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                {{ __('notices.no_translation') }}
                            </span>
                        @endif
                    </div>

                    {{-- Published date --}}
                    <p class="text-xs text-gray-500 mb-3">
                        <time datetime="{{ $notice->published_at?->toIso8601String() }}">
                            {{ __('notices.published_at') }}: {{ $notice->published_at?->translatedFormat('j F Y') }}
                        </time>
                    </p>

                    {{-- Content (truncated) --}}
                    <div class="text-sm text-gray-700 leading-relaxed mb-4">
                        {{ Str::limit(strip_tags($notice->content), 300) }}
                    </div>

                    {{-- Location badges --}}
                    @if ($notice->locations->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5"
                            aria-label="{{ __('notices.portal') }} / {{ __('notices.garage') }}">
                            @foreach ($notice->locations as $location)
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $location->location_type === 'portal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $location->location_type === 'portal' ? __('notices.portal') : __('notices.garage') }}
                                    {{ $location->location_code }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $notices->links() }}
        </div>
    @endif
</div>
