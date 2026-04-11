<div>
    <div class="mb-6 flex items-center justify-end">
        <flux:button variant="primary" wire:click="$set('showAddForm', true)" icon="plus">
            {{ __('admin.locations.add_property') }}
        </flux:button>
    </div>

    @if ($showAddForm)
        <div class="fixed inset-0 z-40" data-section="location-create-form">
            <button type="button" wire:click="$set('showAddForm', false)"
                class="admin-slideover-backdrop absolute inset-0 bg-black/30"
                aria-label="{{ __('general.buttons.cancel') }}"></button>

            <div
                class="admin-slideover-panel absolute inset-y-0 right-0 z-50 h-full w-full max-w-2xl overflow-y-auto bg-white p-6 shadow-2xl">
                <flux:heading size="lg" class="mb-4">{{ __('admin.locations.new_property') }}
                </flux:heading>
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('admin.locations.property_name') }}</flux:label>
                        <flux:input wire:model="newPropertyName"
                            :placeholder="__('admin.locations.property_placeholder')"
                            data-field="new-property-name" />
                        <flux:error name="newPropertyName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin.locations.community_pct') }}</flux:label>
                        <flux:input wire:model="newCommunityPct" type="text"
                            inputmode="decimal" />
                        <flux:error name="newCommunityPct" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('admin.locations.location_pct') }}</flux:label>
                        <flux:input wire:model="newLocationPct" type="text"
                            inputmode="decimal" />
                        <flux:error name="newLocationPct" />
                    </flux:field>
                </div>

                <div
                    class="mt-6 flex flex-wrap items-center justify-end gap-2 border-t border-zinc-200 pt-4">
                    <button type="button" wire:click="addProperty"
                        class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                        {{ __('general.buttons.save') }}
                    </button>
                    <flux:button variant="ghost" wire:click="$set('showAddForm', false)">
                        {{ __('general.buttons.cancel') }}</flux:button>
                </div>
            </div>
        </div>
    @endif

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.property') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.community_pct') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.location_pct') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.locations.assigned') }}</th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </th>
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
                            <flux:input wire:model="editName" size="sm"
                                data-field="edit-name" />
                            <flux:error name="editName" />
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <flux:input wire:model="editCommunityPct" type="text"
                                inputmode="decimal" size="sm" />
                            <flux:error name="editCommunityPct" />
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <flux:input wire:model="editLocationPct" type="text"
                                inputmode="decimal" size="sm" />
                            <flux:error name="editLocationPct" />
                        </td>
                        <td class="px-6 py-4 text-sm" colspan="2">
                            <div class="flex gap-2">
                                <button type="button" wire:click="saveProperty"
                                    class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                    {{ __('general.buttons.save') }}
                                </button>
                                <flux:button variant="ghost" size="sm"
                                    wire:click="cancelEditing">
                                    {{ __('general.buttons.cancel') }}
                                </flux:button>
                            </div>
                        </td>
                    @else
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $property->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            %{{ number_format($property->community_pct ?? 0, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            %{{ number_format($property->location_pct ?? 0, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <flux:badge :color="$isAssigned ? 'green' : 'zinc'"
                                data-assigned="{{ $isAssigned ? 'yes' : 'no' }}">
                                {{ $isAssigned ? __('admin.common.yes') : __('admin.common.no') }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <flux:button variant="ghost" size="sm"
                                wire:click="startEditing({{ $property->id }})" icon="pencil">
                                {{ __('general.buttons.edit') }}
                            </flux:button>
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
