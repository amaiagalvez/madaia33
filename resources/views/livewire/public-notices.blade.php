<div>
    {{-- Filter by location --}}
    <div class="mb-8">
        <label for="location-filter" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('notices.filter.label') }}
        </label>
        <select id="location-filter" wire:model.live="locationFilter"
            class="block w-full sm:w-64 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500">
            <option value="">{{ __('notices.filter.all') }}</option>
            <optgroup label="{{ __('notices.portal') }}">
                @foreach (\App\CommunityLocations::PORTALS as $portal)
                    <option value="{{ $portal }}">{{ __('notices.portal') }}
                        {{ $portal }}</option>
                @endforeach
            </optgroup>
            <optgroup label="{{ __('notices.garage') }}">
                @foreach (\App\CommunityLocations::GARAGES as $floor)
                    <option value="{{ $floor }}">{{ __('notices.garage') }}
                        {{ $floor }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>

    {{-- Notices list --}}
    {{-- Notices grid --}}
    @if ($notices->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
            <p class="text-gray-500 text-sm">{{ __('notices.empty') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8"
            data-notices-grid>
            @foreach ($notices as $notice)
                <x-notice-card :notice="$notice" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $notices->links() }}
        </div>
    @endif
</div>
