<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">{{ $location->name }} ({{ $location->code }})</flux:heading>
        <flux:button variant="primary" wire:click="$set('showAddForm', true)" icon="plus">
            {{ __('admin.locations.add_property') }}
        </flux:button>
    </div>

    @if ($showAddForm)
        <div
            class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('admin.locations.new_property') }}</flux:heading>
            <div class="flex items-end gap-3">
                <flux:field class="flex-1">
                    <flux:label>{{ __('admin.locations.property_name') }}</flux:label>
                    <flux:input wire:model="newPropertyName" :placeholder="__('admin.locations.property_placeholder')"
                        data-field="new-property-name" />
                    <flux:error name="newPropertyName" />
                </flux:field>
                <flux:button variant="primary" wire:click="addProperty">{{ __('general.buttons.save') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="$set('showAddForm', false)">
                    {{ __('general.buttons.cancel') }}</flux:button>
            </div>
        </div>
    @endif

    <div
        class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="min-w-full text-sm">
            <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr class="text-left text-zinc-600 dark:text-zinc-300">
                    <th class="px-4 py-3">{{ __('admin.locations.property') }}</th>
                    @if ($location->type !== 'storage')
                        <th class="px-4 py-3">{{ __('admin.locations.community_pct') }}</th>
                        <th class="px-4 py-3">{{ __('admin.locations.location_pct') }}</th>
                    @endif
                    <th class="px-4 py-3">{{ __('admin.locations.assigned') }}</th>
                    <th class="px-4 py-3">{{ __('admin.locations.admin_validated') }}</th>
                    <th class="px-4 py-3">{{ __('admin.locations.owner_validated') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $property)
                    @php
                        $activeAssignment = $property->activeAssignments->first();
                        $isAssigned = $activeAssignment !== null;
                    @endphp
                    <tr wire:key="property-{{ $property->id }}"
                        data-property-id="{{ $property->id }}"
                        class="border-b border-zinc-100 dark:border-zinc-700">
                        @if ($editingPropertyId === $property->id)
                            <td class="px-4 py-3">
                                <flux:input wire:model="editName" size="sm"
                                    data-field="edit-name" />
                                <flux:error name="editName" />
                            </td>
                            @if ($location->type !== 'storage')
                                <td class="px-4 py-3">
                                    <flux:input wire:model="editCommunityPct" type="number"
                                        step="0.0001" size="sm" />
                                    <flux:error name="editCommunityPct" />
                                </td>
                                <td class="px-4 py-3">
                                    <flux:input wire:model="editLocationPct" type="number"
                                        step="0.0001" size="sm" />
                                    <flux:error name="editLocationPct" />
                                </td>
                            @endif
                            <td class="px-4 py-3"
                                colspan="{{ $location->type === 'storage' ? 4 : 3 }}">
                                <div class="flex gap-2">
                                    <flux:button variant="primary" size="sm"
                                        wire:click="saveProperty">{{ __('general.buttons.save') }}</flux:button>
                                    <flux:button variant="ghost" size="sm"
                                        wire:click="cancelEditing">{{ __('general.buttons.cancel') }}
                                    </flux:button>
                                </div>
                            </td>
                        @else
                            <td class="px-4 py-3">{{ $property->name }}</td>
                            @if ($location->type !== 'storage')
                                <td class="px-4 py-3">{{ $property->community_pct ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $property->location_pct ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <flux:badge :color="$isAssigned ? 'green' : 'zinc'"
                                    data-assigned="{{ $isAssigned ? 'yes' : 'no' }}">
                                    {{ $isAssigned ? __('admin.common.yes') : __('admin.common.no') }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge
                                    :color="$isAssigned && $activeAssignment->admin_validated ? 'green' : 'zinc'"
                                    data-admin-validated="{{ $isAssigned && $activeAssignment?->admin_validated ? 'yes' : 'no' }}">
                                    {{ $isAssigned && $activeAssignment?->admin_validated ? __('admin.common.yes') : __('admin.common.no') }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge
                                    :color="$isAssigned && $activeAssignment->owner_validated ? 'green' : 'zinc'"
                                    data-owner-validated="{{ $isAssigned && $activeAssignment?->owner_validated ? 'yes' : 'no' }}">
                                    {{ $isAssigned && $activeAssignment?->owner_validated ? __('admin.common.yes') : __('admin.common.no') }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:button variant="ghost" size="sm"
                                    wire:click="startEditing({{ $property->id }})" icon="pencil">
                                    {{ __('general.buttons.edit') }}
                                </flux:button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-zinc-400">
                            {{ __('admin.locations.no_properties') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
