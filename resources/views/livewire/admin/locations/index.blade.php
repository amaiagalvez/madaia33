<div>
    {{-- Type tabs --}}
    <div class="mb-6 flex gap-2">
        @foreach ($types as $typeKey)
            <flux:button :variant="$type === $typeKey ? 'primary' : 'ghost'"
                wire:click="setType('{{ $typeKey }}')" data-type="{{ $typeKey }}">
                {{ __('admin.locations.types.' . $typeKey) }}
            </flux:button>
        @endforeach
    </div>

    @php
        $hasCommunityPctWarning = $locations->some(function ($location) {
            $total = (float) ($location->properties_sum_community_pct ?? 0);
            return abs($total - 100.0) > 0.01;
        });
    @endphp

    @if ($hasCommunityPctWarning)
        <flux:callout color="red" class="mb-6">
            {{ __('admin.locations.community_pct_must_be_100') }}
        </flux:callout>
    @endif

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.code') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.name') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.properties_count') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.total_community_pct') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.total_location_pct') }}</th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('admin.locations.view') }}</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($locations as $location)
                <tr wire:key="location-{{ $location->id }}" data-location-id="{{ $location->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $location->code }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $location->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $location->properties_count }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        %{{ number_format((float) ($location->properties_sum_community_pct ?? 0), 2, '.', '') }}
                    </td>
                    @php
                        $totalLocationPct = (float) ($location->properties_sum_location_pct ?? 0);
                        $isInvalid = abs($totalLocationPct - 100.0) > 0.01;
                    @endphp
                    <td class="px-6 py-4 text-sm {{ $isInvalid ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                        %{{ number_format($totalLocationPct, 2, '.', '') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('admin.locations.show', $location) }}"
                            title="{{ __('admin.locations.view') }}"
                            class="inline-flex items-center rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]">
                            <flux:icon.eye class="size-4" />
                            <span class="sr-only">{{ __('admin.locations.view') }}</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('admin.locations.no_records') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    <div class="mt-4">
        {{ $locations->links() }}
    </div>
</div>
