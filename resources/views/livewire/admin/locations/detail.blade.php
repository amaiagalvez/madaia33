<div>
    @if ($isChiefSelectable)
        @php
            $currentChiefProperty = $chiefProperties->firstWhere('id', (int) $chiefPropertyId);
            $currentChiefAssignment = $currentChiefProperty?->activeAssignments?->firstWhere(
                'owner_id',
                $currentChiefOwnerId,
            );
            $currentChief = $currentChiefAssignment?->owner;
        @endphp

        <div id="location-chief-form"
            class="mb-6 rounded-lg border border-[#edd2c7] bg-[#edd2c7]/20 p-4"
            data-section="location-chief-form">
            <div class="flex flex-wrap items-end gap-3">
                <div class="grow">
                    @if ($canManageChiefAssignment)
                        <label for="chief-owner-id"
                            class="mb-1 block text-sm font-medium text-stone-700">
                            {{ __('admin.locations.chief_property') }}
                        </label>
                        <select id="chief-owner-id" wire:model="chiefPropertyId"
                            class="w-full max-w-64 rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]"
                            data-field="chief-owner-id">
                            <option value="">
                                {{ __('admin.locations.chief_property_placeholder') }}
                            </option>
                            @foreach ($chiefProperties as $candidateProperty)
                                <option value="{{ $candidateProperty->id }}">
                                    {{ $candidateProperty->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <p class="mb-1 block text-sm font-medium text-stone-700">
                            {{ __('admin.locations.chief_property') }}
                        </p>
                        <p class="w-full max-w-64 rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700"
                            data-field="chief-owner-readonly">
                            {{ $currentChiefProperty?->name ?? '—' }}
                        </p>
                    @endif
                </div>

                @if ($canManageChiefAssignment)
                    <x-admin.form-footer-actions class="mt-0">
                        <button type="button" wire:click="saveChiefOwner"
                            class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                            {{ __('admin.locations.chief_owner_save') }}
                        </button>
                    </x-admin.form-footer-actions>
                @endif
            </div>

            @error('chiefPropertyId')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-stone-600">
                {{ __('admin.locations.chief_property_help') }}</p>

            @if ($currentChief !== null)
                <p class="mt-3 text-sm text-stone-700"
                    data-chief-owner-current="{{ $currentChief->id }}">
                    {{ __('admin.locations.chief_owner_current', ['name' => $currentChief->coprop1_name]) }}
                </p>
            @endif
        </div>
    @endif

    @if ($canManagePropertyCrud)
        <div class="mb-6 flex items-center justify-end">
            <x-admin.create-record-button wire:click="openAddForm">
                {{ __('admin.locations.add_property') }}
            </x-admin.create-record-button>
        </div>
    @endif

    @if ($showAddForm)
        <x-admin.side-panel-form section="location-create-form"
            card-id="admin-location-property-form-card" cancel-action="cancelAddForm">
            <form wire:submit="addProperty" novalidate>
                <h2 class="mb-4 text-lg font-semibold text-stone-900">
                    {{ __('admin.locations.new_property') }}
                </h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.form-input name="newPropertyName" model="newPropertyName"
                        :label="__('admin.locations.property_name')" class="md:col-span-2" data-field="new-property-name" />

                    <x-admin.form-input name="newCommunityPct" model="newCommunityPct"
                        :label="__('admin.locations.community_pct')" />

                    <x-admin.form-input name="newLocationPct" model="newLocationPct"
                        :label="__('admin.locations.location_pct')" />
                </div>

                <x-admin.form-footer-actions show-default-buttons
                    class="mt-6 justify-end border-t border-zinc-200 pt-4" :is-editing="true"
                    cancel-action="cancelAddForm" :save-label="__('general.buttons.save')" />
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.property') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.community_pct') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.location_pct') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.locations.assigned') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($properties as $property)
                @php
                    $activeAssignment = $property->activeAssignments->first();
                    $isAssigned = $activeAssignment !== null;
                @endphp
                <tr wire:key="property-{{ $property->id }}"
                    data-property-id="{{ $property->id }}">
                    @if ($editingPropertyId === $property->id)
                        <td class="px-6 py-4 text-sm">
                            <input type="text" wire:model="editName"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]"
                                data-field="edit-name" />
                            @error('editName')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <input type="text" wire:model="editCommunityPct" inputmode="decimal"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                            @error('editCommunityPct')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <input type="text" wire:model="editLocationPct" inputmode="decimal"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                            @error('editLocationPct')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-6 py-4 text-sm" colspan="2">
                            <x-admin.form-footer-actions class="mt-0">
                                <button type="button" wire:click="saveProperty"
                                    class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                    {{ __('general.buttons.save') }}
                                </button>
                                <flux:button variant="ghost" size="sm"
                                    wire:click="cancelEditing">
                                    {{ __('general.buttons.cancel') }}
                                </flux:button>
                            </x-admin.form-footer-actions>
                        </td>
                    @else
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $property->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            %{{ number_format($property->community_pct ?? 0, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            %{{ number_format($property->location_pct ?? 0, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <x-admin.status-indicator :active="$isAssigned"
                                data-assigned="{{ $isAssigned ? 'yes' : 'no' }}" />
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            @if ($canManagePropertyCrud)
                                <x-admin.table-row-actions>
                                    <x-admin.icon-button-edit
                                        wire:click="startEditing({{ $property->id }})"
                                        :title="__('general.buttons.edit')" />
                                </x-admin.table-row-actions>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('admin.locations.no_properties') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>
</div>
