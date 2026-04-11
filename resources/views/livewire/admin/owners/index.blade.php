<div>
    <div class="mb-4 flex items-center justify-end gap-2">
        <x-admin.create-record-button wire:click="$set('showCreateForm', true)" />
    </div>

    @if ($showCreateForm)
        <div class="fixed inset-0 z-40" data-section="owner-create-form">
            <button type="button" wire:click="cancelCreateOwner"
                class="admin-slideover-backdrop absolute inset-0 bg-black/30"
                aria-label="{{ __('general.buttons.cancel') }}"></button>

            <div
                class="admin-slideover-panel absolute inset-y-0 right-0 z-50 h-full w-full max-w-4xl overflow-y-auto bg-white p-6 shadow-2xl">
                <flux:heading size="lg" class="mb-4">{{ __('admin.owners.create') }}
                </flux:heading>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 p-4">
                        <flux:heading size="sm" class="mb-3 text-zinc-800">
                            {{ __('admin.owners.columns.coprop1') }}
                        </flux:heading>

                        <div class="grid gap-3">
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_name') }}</flux:label>
                                <flux:input wire:model="coprop1Name" />
                                <flux:error name="coprop1Name" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_dni') }}</flux:label>
                                <flux:input wire:model="coprop1Dni" />
                                <flux:error name="coprop1Dni" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_phone') }}</flux:label>
                                <flux:input wire:model="coprop1Phone" />
                                <flux:error name="coprop1Phone" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_email') }}</flux:label>
                                <flux:input wire:model="coprop1Email" type="email" />
                                <flux:error name="coprop1Email" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4">
                        <flux:heading size="sm" class="mb-3 text-zinc-800">
                            {{ __('admin.owners.columns.coprop2') }}
                        </flux:heading>

                        <div class="grid gap-3">
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop2_name') }}</flux:label>
                                <flux:input wire:model="coprop2Name" />
                                <flux:error name="coprop2Name" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop2_dni') }}</flux:label>
                                <flux:input wire:model="coprop2Dni" />
                                <flux:error name="coprop2Dni" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop2_phone') }}
                                </flux:label>
                                <flux:input wire:model="coprop2Phone" />
                                <flux:error name="coprop2Phone" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop2_email') }}
                                </flux:label>
                                <flux:input wire:model="coprop2Email" type="email" />
                                <flux:error name="coprop2Email" />
                            </flux:field>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border border-zinc-200 p-4"
                    data-section="owner-create-assignments">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <flux:heading size="md">{{ __('admin.owners.assignments') }}
                        </flux:heading>
                        <flux:button variant="ghost" wire:click="addAssignmentRow" size="sm"
                            icon="plus">
                            {{ __('admin.owners.add_property') }}
                        </flux:button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($newAssignments as $index => $assignment)
                            <div wire:key="assignment-row-{{ $index }}"
                                class="grid gap-3 md:grid-cols-2 xl:grid-cols-12 xl:items-end">
                                <flux:field class="min-w-0 md:col-span-2 xl:col-span-6">
                                    <flux:label>{{ __('admin.owners.property') }}</flux:label>
                                    <flux:select
                                        wire:model="newAssignments.{{ $index }}.property_id">
                                        <flux:select.option value="">
                                            {{ __('admin.owners.select_property') }}
                                        </flux:select.option>
                                        @foreach ($assignableProperties as $property)
                                            <flux:select.option :value="$property->id">
                                                {{ $property->location->code }} -
                                                {{ __('admin.locations.types.' . $property->location->type) }}
                                                - {{ $property->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error
                                        name="newAssignments.{{ $index }}.property_id" />
                                </flux:field>

                                <flux:field class="xl:col-span-2">
                                    <flux:label>{{ __('admin.owners.start_date') }}</flux:label>
                                    <flux:input type="date"
                                        wire:model="newAssignments.{{ $index }}.start_date" />
                                    <flux:error
                                        name="newAssignments.{{ $index }}.start_date" />
                                </flux:field>

                                <flux:field class="xl:col-span-2">
                                    <flux:label>{{ __('admin.owners.end_date') }}</flux:label>
                                    <flux:input type="date"
                                        wire:model="newAssignments.{{ $index }}.end_date" />
                                    <flux:error
                                        name="newAssignments.{{ $index }}.end_date" />
                                </flux:field>

                                <div
                                    class="flex items-center justify-end md:col-span-2 xl:col-span-2 xl:justify-end">
                                    <flux:button variant="ghost"
                                        wire:click="removeAssignmentRow({{ $index }})"
                                        icon="trash" size="sm">
                                        {{ __('general.buttons.delete') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button type="button" wire:click="createOwner"
                        class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                        {{ __('admin.owners.create_submit') }}
                    </button>
                    <flux:button variant="ghost" wire:click="cancelCreateOwner">
                        {{ __('general.buttons.cancel') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 overflow-x-auto" data-section="filters">
        <div class="flex min-w-max flex-nowrap items-end gap-3" data-layout="single-row-filters">
            <flux:select wire:model.live="filterStatus" data-filter="status">
                <flux:select.option value="active">{{ __('admin.owners.filters.active') }}
                </flux:select.option>
                <flux:select.option value="inactive">{{ __('admin.owners.filters.inactive') }}
                </flux:select.option>
                <flux:select.option value="all">{{ __('admin.owners.filters.all') }}
                </flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filterPortal" data-filter="portal">
                <flux:select.option value="">{{ __('admin.owners.filters.all_portals') }}
                </flux:select.option>
                @foreach ($portals as $portal)
                    <flux:select.option :value="$portal->id">{{ $portal->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterLocal" data-filter="local">
                <flux:select.option value="">{{ __('admin.owners.filters.all_locals') }}
                </flux:select.option>
                @foreach ($locals as $local)
                    <flux:select.option :value="$local->id">{{ $local->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterGarage" data-filter="garage">
                <flux:select.option value="">{{ __('admin.owners.filters.all_garages') }}
                </flux:select.option>
                @foreach ($garages as $garage)
                    <flux:select.option :value="$garage->id">{{ $garage->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStorage" data-filter="storage">
                <flux:select.option value="">{{ __('admin.owners.filters.all_storages') }}
                </flux:select.option>
                @foreach ($storages as $storage)
                    <flux:select.option :value="$storage->id">{{ $storage->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            @if ($ownershipView === 'without_properties')
                <flux:button variant="ghost" wire:click="clearWithoutProperties"
                    data-action="show-all-owners">
                    {{ __('admin.owners.filters.back_to_default') }}
                </flux:button>
            @else
                <flux:button variant="ghost" wire:click="showWithoutProperties"
                    data-action="show-owners-without-properties">
                    {{ __('admin.owners.filters.without_properties') }}
                </flux:button>
            @endif
        </div>
    </div>

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.owners.columns.coprop1') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.owners.columns.coprop2') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.owners.columns.portals') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.owners.columns.locals') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.owners.columns.garages') }}</th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.owners.columns.storages') }}</th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($owners as $owner)
                <tr wire:key="owner-{{ $owner->id }}" data-owner-id="{{ $owner->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $owner->coprop1_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $owner->coprop2_name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $portalAssignments = $owner->assignments->filter(
                                fn($a) => $a->property->location->type === 'portal',
                            );
                        @endphp
                        @foreach ($portalAssignments as $a)
                            <span
                                class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                {{ $a->property->location->code }} {{ $a->property->name }}
                            </span><br>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $localAssignments = $owner->assignments->filter(
                                fn($a) => $a->property->location->type === 'local',
                            );
                        @endphp
                        @foreach ($localAssignments as $a)
                            <span
                                class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                {{ $a->property->location->code }} {{ $a->property->name }}
                            </span><br>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $garageAssignments = $owner->assignments->filter(
                                fn($a) => $a->property->location->type === 'garage',
                            );
                        @endphp
                        @foreach ($garageAssignments as $a)
                            <span
                                class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                {{ $a->property->location->code }} {{ $a->property->name }}
                            </span><br>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $storageAssignments = $owner->assignments->filter(
                                fn($a) => $a->property->location->type === 'storage',
                            );
                        @endphp
                        @foreach ($storageAssignments as $a)
                            <span
                                class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                {{ $a->property->location->code }} {{ $a->property->name }}
                            </span><br>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <button type="button" wire:click="toggleOwnerRow({{ $owner->id }})"
                            title="{{ __('general.buttons.edit') }}"
                            data-action="toggle-owner-inline-{{ $owner->id }}"
                            class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]">
                            <flux:icon.pencil-square class="size-4" />
                        </button>
                    </td>
                </tr>

                @if ($expandedOwnerId === $owner->id)
                    <tr wire:key="owner-inline-panel-{{ $owner->id }}" class="bg-gray-50">
                        <td colspan="7" class="px-6 py-4">
                            <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4"
                                data-owner-inline-panel="{{ $owner->id }}">
                                @if ($rowErrorMessage !== '')
                                    <div
                                        class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                        {{ $rowErrorMessage }}
                                    </div>
                                @endif

                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr
                                                class="border-b border-gray-200 text-left text-xs uppercase tracking-wider text-gray-500">
                                                <th class="px-3 py-2">
                                                    {{ __('admin.owners.location') }}</th>
                                                <th class="px-3 py-2">
                                                    {{ __('admin.owners.property') }}</th>
                                                <th class="px-3 py-2">
                                                    {{ __('admin.owners.start_date') }}</th>
                                                <th class="px-3 py-2">
                                                    {{ __('admin.owners.end_date') }}</th>
                                                <th class="px-3 py-2">
                                                    {{ __('admin.owners.admin_validated') }}</th>
                                                <th class="px-3 py-2">
                                                    {{ __('admin.owners.owner_validated') }}</th>
                                                <th class="px-3 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($expandedAssignments as $assignment)
                                                <tr wire:key="owner-inline-assignment-{{ $assignment->id }}"
                                                    class="border-b border-gray-100">
                                                    <td class="px-3 py-2">
                                                        {{ $assignment->property->location->code }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        {{ $assignment->property->name }}</td>
                                                    <td class="px-3 py-2">
                                                        <flux:input type="date" size="sm"
                                                            wire:model="assignmentEdits.{{ $assignment->id }}.start_date" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <flux:input type="date" size="sm"
                                                            wire:model="assignmentEdits.{{ $assignment->id }}.end_date" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="checkbox"
                                                            class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]"
                                                            wire:model="assignmentEdits.{{ $assignment->id }}.admin_validated"
                                                            @disabled($assignment->end_date !== null)>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="checkbox"
                                                            class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]"
                                                            wire:model="assignmentEdits.{{ $assignment->id }}.owner_validated"
                                                            @disabled($assignment->end_date !== null)>
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                        <button type="button"
                                                            wire:click="saveAssignment({{ $assignment->id }})"
                                                            class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                                            {{ __('general.buttons.save') }}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7"
                                                        class="px-3 py-3 text-sm text-gray-500">
                                                        {{ __('admin.owners.no_assignments') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="grid gap-3 md:grid-cols-12 md:items-end"
                                    data-owner-inline-create="{{ $owner->id }}">
                                    <flux:field class="md:col-span-5">
                                        <flux:label>{{ __('admin.owners.property') }}</flux:label>
                                        <flux:select wire:model="inlinePropertyId">
                                            <flux:select.option value="">
                                                {{ __('admin.owners.select_property') }}
                                            </flux:select.option>
                                            @foreach ($assignableProperties as $property)
                                                <flux:select.option :value="$property->id">
                                                    {{ $property->location->code }} -
                                                    {{ __('admin.locations.types.' . $property->location->type) }}
                                                    - {{ $property->name }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="inlinePropertyId" />
                                    </flux:field>

                                    <flux:field class="md:col-span-3">
                                        <flux:label>{{ __('admin.owners.start_date') }}
                                        </flux:label>
                                        <flux:input type="date" wire:model="inlineStartDate" />
                                        <flux:error name="inlineStartDate" />
                                    </flux:field>

                                    <flux:field class="md:col-span-3">
                                        <flux:label>{{ __('admin.owners.end_date') }}</flux:label>
                                        <flux:input type="date" wire:model="inlineEndDate" />
                                        <flux:error name="inlineEndDate" />
                                    </flux:field>

                                    <div class="md:col-span-1 md:flex md:justify-end">
                                        <button type="button" wire:click="createInlineAssignment"
                                            class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                            {{ __('general.buttons.create_new') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('admin.owners.no_records') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    <div class="mt-4" data-section="owners-pagination">
        {{ $owners->links() }}
    </div>
</div>
