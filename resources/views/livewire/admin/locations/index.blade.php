<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error_message'))
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
            {{ session('error_message') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-end">
        @unless ($showCreateForm || $showEditForm)
            <x-admin.create-record-button wire:click="createLocation">
            </x-admin.create-record-button>
        @endunless
    </div>

    {{-- Type tabs --}}
    <div class="mb-6">
        <x-admin.filter-toggle-group class="flex-wrap" data-locations-type-filters>
            @foreach ($types as $typeKey)
                <x-admin.filter-toggle-button wire:click="setType('{{ $typeKey }}')"
                    data-type="{{ $typeKey }}" :key="$typeKey" :active="$type === $typeKey">
                    {{ __('admin.locations.types.' . $typeKey) }}
                </x-admin.filter-toggle-button>
            @endforeach
        </x-admin.filter-toggle-group>
    </div>

    @php
        $hasCommunityPctWarning = $locations->some(function ($location) {
            $total = (float) ($location->properties_sum_community_pct ?? 0);
            return abs($total - 100.0) > 0.01;
        });
    @endphp

    @if ($showEditForm)
        <x-admin.side-panel-form section="location-edit-form" card-id="admin-location-edit-form-card"
            cancel-action="cancelEditForm">
            <form wire:submit="saveEditForm" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <x-admin.form-input name="editCode" model="editCode" :label="__('admin.locations.code')" />
                    <x-admin.form-input name="editName" model="editName" :label="__('admin.locations.name')" />
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="true"
                    cancel-action="cancelEditForm" />
            </form>
        </x-admin.side-panel-form>
    @endif

    @if ($showCreateForm)
        <x-admin.side-panel-form section="location-create-form"
            card-id="admin-location-create-form-card" cancel-action="cancelCreateForm">
            <form wire:submit="saveCreateForm" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('admin.locations.type') }}
                        </label>
                        <p
                            class="rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            {{ __('admin.locations.types.' . $type) }}
                        </p>
                    </div>

                    <x-admin.form-input name="newCode" model="newCode" :label="__('admin.locations.code')" />
                    <x-admin.form-input name="newName" model="newName" :label="__('admin.locations.name')" />
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="false"
                    cancel-action="cancelCreateForm" />
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.code') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.name') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.properties_count') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.total_community_pct') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.total_location_pct') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.chief_property') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.community_admin_name') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('admin.locations.view') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($locations as $location)
                <tr wire:key="location-{{ $location->id }}"
                    data-location-id="{{ $location->id }}">
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
                    <td
                        class="px-6 py-4 text-sm {{ $isInvalid ? 'text-red-900 font-semibold' : 'text-gray-500' }}">
                        %{{ number_format($totalLocationPct, 2, '.', '') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"
                        data-chief-property-for="{{ $location->id }}">
                        {{ $location->chief_property_name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"
                        data-community-admin-for="{{ $location->id }}">
                        {{ $location->community_admin_name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions :bars-href="route('admin.locations.show', $location)" :bars-title="__('admin.locations.view_properties')"
                            :bars-sr-text="__('admin.locations.view_properties')">
                            <x-admin.icon-button-edit
                                wire:click="openEditForm({{ $location->id }})"
                                :title="__('admin.locations.edit_location')" />
                            <x-admin.icon-button-delete
                                wire:click="confirmDelete({{ $location->id }})"
                                :title="__('general.buttons.delete')" />
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('admin.locations.no_records') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    <div class="mt-4">
        {{ $locations->links() }}
    </div>

    @if ($hasCommunityPctWarning)
        <p class="mt-4 text-sm font-semibold text-red-700">
            {{ __('admin.locations.community_pct_must_be_100') }}
        </p>
    @endif

    @if ($showDeleteModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="delete-location-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="delete-location-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('admin.locations.delete_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('admin.locations.confirm_delete') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteLocation"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
