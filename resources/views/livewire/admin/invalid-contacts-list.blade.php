<div class="space-y-6" data-invalid-contacts-page>
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200" data-invalid-contacts-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>{{ __('campaigns.admin.recipient_name') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.slot') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.contact') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.channel') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.consecutive_errors') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.last_error') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('campaigns.admin.actions.mark_as_valid') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($rows as $row)
                <tr wire:key="invalid-contact-{{ $row['owner_id'] }}-{{ $row['slot'] }}-{{ $row['channel'] }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['name'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['slot_label'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['contact'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['channel_label'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['errors'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['last_error_at']?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <button type="button"
                            wire:click="markAsValid({{ $row['owner_id'] }}, '{{ $row['slot'] }}', '{{ $row['channel'] }}')"
                            class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                            {{ __('campaigns.admin.actions.mark_as_valid') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('campaigns.admin.empty_invalid_contacts') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>
</div>
