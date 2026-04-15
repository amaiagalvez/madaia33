<div>
    <div class="mb-4 flex items-center justify-end gap-2">
        <x-admin.create-record-button wire:click="$set('showCreateForm', true)" />
    </div>

    @if ($showEditOwnerForm)
        <x-admin.side-panel-form section="owner-edit-form" card-id="admin-owner-edit-form-card"
            cancel-action="cancelEditOwner">
            <form wire:submit="saveEditOwner" novalidate class="text-gray-900">
                <flux:heading size="lg" class="mb-4">{{ __('admin.owners.edit_owner') }}
                </flux:heading>

                <x-admin.owner-shared-fields mode="wire" coprop1-name-model="editCoprop1Name"
                    coprop1-surname-model="editCoprop1Surname" coprop1-dni-model="editCoprop1Dni"
                    coprop1-phone-model="editCoprop1Phone" coprop1-email-model="editCoprop1Email"
                    language-model="editLanguage" coprop2-name-model="editCoprop2Name"
                    coprop2-dni-model="editCoprop2Dni" coprop2-surname-model="editCoprop2Surname"
                    coprop2-phone-model="editCoprop2Phone" coprop2-email-model="editCoprop2Email" />

                <details class="mt-6 rounded-lg border border-zinc-200 bg-gray-50"
                    data-section="owner-audit-log">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-zinc-800">
                        <span>{{ __('admin.owners.audit.title') }}
                            ({{ $editOwnerAuditLogCount }})</span>
                        <span class="text-xs font-medium text-zinc-500">
                            {{ __('admin.owners.audit.latest_limit', ['count' => count($editOwnerAuditLogs)]) }}
                        </span>
                    </summary>

                    <div class="border-t border-zinc-200 px-4 py-4">
                        @if ($editOwnerAuditLogCount === 0)
                            <div
                                class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-sm text-gray-500">
                                {{ __('admin.owners.audit.empty') }}
                            </div>
                        @else
                            <div class="max-h-72 overflow-y-auto" data-owner-audit-log-scroll>
                                <x-admin.panel-table
                                    table-class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <x-admin.table-header-cell class="px-4 py-2">
                                                {{ __('admin.owners.audit.date') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-4 py-2">
                                                {{ __('admin.owners.audit.field') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-4 py-2">
                                                {{ __('admin.owners.audit.old_value') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-4 py-2">
                                                {{ __('admin.owners.audit.new_value') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-4 py-2">
                                                {{ __('admin.owners.audit.changed_by') }}
                                            </x-admin.table-header-cell>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach ($editOwnerAuditLogs as $auditLog)
                                            <tr>
                                                <td class="px-4 py-2 text-xs text-gray-500">
                                                    {{ $auditLog['changed_at'] }}</td>
                                                <td
                                                    class="px-4 py-2 text-sm font-medium text-gray-900">
                                                    {{ $auditLog['field_label'] }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600">
                                                    {{ $auditLog['old_value'] }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600">
                                                    {{ $auditLog['new_value'] }}</td>
                                                <td class="px-4 py-2 text-xs text-gray-500">
                                                    {{ $auditLog['changed_by'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </x-admin.panel-table>
                            </div>
                        @endif
                    </div>
                </details>

                <x-admin.form-footer-actions show-default-buttons :is-editing="true"
                    :save-label="__('admin.owners.edit_submit')" cancel-action="cancelEditOwner" />
            </form>
        </x-admin.side-panel-form>
    @endif

    @if ($showCreateForm)
        <x-admin.side-panel-form section="owner-create-form" card-id="admin-owner-create-form-card"
            cancel-action="cancelCreateOwner">
            <form wire:submit="createOwner" novalidate class="text-gray-900">
                <flux:heading size="lg" class="mb-4">{{ __('admin.owners.create') }}
                </flux:heading>

                <div class="mb-4">
                    <flux:field>
                        <flux:label>{{ __('admin.owners.form.id') }}</flux:label>
                        <flux:input wire:model="ownerId" type="text" inputmode="numeric" />
                        <flux:error name="ownerId" />
                    </flux:field>
                </div>

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
                                <flux:label>{{ __('admin.owners.form.coprop1_surname') }}
                                </flux:label>
                                <flux:input wire:model="coprop1Surname" />
                                <flux:error name="coprop1Surname" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_dni') }}</flux:label>
                                <flux:input wire:model="coprop1Dni" />
                                <flux:error name="coprop1Dni" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_phone') }}
                                </flux:label>
                                <flux:input wire:model="coprop1Phone" />
                                <flux:error name="coprop1Phone" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.coprop1_email') }}
                                </flux:label>
                                <flux:input wire:model="coprop1Email" type="email" />
                                <flux:error name="coprop1Email" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('admin.owners.form.language') }}</flux:label>
                                <flux:select wire:model="language">
                                    <flux:select.option value="eu">
                                        {{ __('general.language.eu') }}</flux:select.option>
                                    <flux:select.option value="es">
                                        {{ __('general.language.es') }}</flux:select.option>
                                </flux:select>
                                <flux:error name="language" />
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
                                <flux:label>{{ __('admin.owners.form.coprop2_surname') }}
                                </flux:label>
                                <flux:input wire:model="coprop2Surname" />
                                <flux:error name="coprop2Surname" />
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
                        <h3 class="text-base font-semibold text-zinc-800">
                            {{ __('admin.owners.assignments') }}</h3>
                        <button type="button" wire:click="addAssignmentRow"
                            class="inline-flex items-center gap-1 rounded-md border border-[#d9755b]/30 px-3 py-1.5 text-sm font-medium text-[#793d3d] hover:bg-[#edd2c7]/40 hover:text-[#d9755b]">
                            <flux:icon.plus class="size-4" />
                            <span>{{ __('admin.owners.add_property') }}</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($newAssignments as $index => $assignment)
                            <div wire:key="assignment-row-{{ $index }}"
                                class="rounded-lg border border-zinc-200 bg-white p-3">
                                <flux:field class="min-w-0">
                                    <flux:label class="text-stone-700">
                                        {{ __('admin.owners.property') }}</flux:label>
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

                                <div
                                    class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:items-end">
                                    <x-admin.form-date-input :label="__('admin.owners.start_date')" :model="'newAssignments.' . $index . '.start_date'"
                                        :id="'new-assignment-start-' . $index" :name="'newAssignments.' . $index . '.start_date'"
                                        container-class="min-w-0" />

                                    <x-admin.form-date-input :label="__('admin.owners.end_date')" :model="'newAssignments.' . $index . '.end_date'"
                                        :id="'new-assignment-end-' . $index" :name="'newAssignments.' . $index . '.end_date'"
                                        container-class="min-w-0" />

                                    <div class="flex items-end justify-end">
                                        <flux:button variant="ghost"
                                            wire:click="removeAssignmentRow({{ $index }})"
                                            icon="trash" size="sm">
                                            {{ __('general.buttons.delete') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="false"
                    :save-label="__('admin.owners.create_submit')" cancel-action="cancelCreateOwner" />
            </form>
        </x-admin.side-panel-form>
    @endif

    <div class="mb-6 overflow-x-auto" data-section="filters">
        <div class="flex min-w-max flex-nowrap items-end gap-3" data-layout="single-row-filters">
            <x-admin.filter-input id="owners-search" :label="__('admin.owners.filters.search')" :placeholder="__('admin.owners.filters.search_placeholder')"
                wire:model.live.debounce.300ms="filterSearch" />

            <select wire:model.live="filterStatus" data-filter="status"
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                <option value="active">{{ __('admin.owners.filters.active') }}</option>
                <option value="inactive">{{ __('admin.owners.filters.inactive') }}</option>
                <option value="all">{{ __('admin.owners.filters.all') }}</option>
            </select>

            <select wire:model.live="filterPortal" data-filter="portal"
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                <option value="">{{ __('admin.owners.filters.all_portals') }}</option>
                @foreach ($portals as $portal)
                    <option value="{{ $portal->id }}">{{ $portal->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterLocal" data-filter="local"
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                <option value="">{{ __('admin.owners.filters.all_locals') }}</option>
                @foreach ($locals as $local)
                    <option value="{{ $local->id }}">{{ $local->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterGarage" data-filter="garage"
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                <option value="">{{ __('admin.owners.filters.all_garages') }}</option>
                @foreach ($garages as $garage)
                    <option value="{{ $garage->id }}">{{ $garage->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStorage" data-filter="storage"
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                <option value="">{{ __('admin.owners.filters.all_storages') }}</option>
                @foreach ($storages as $storage)
                    <option value="{{ $storage->id }}">{{ $storage->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.num') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.coprop1') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.coprop2') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.portals') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.locals') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.garages') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.storages') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.welcome') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.owners.columns.terms_accepted') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($owners as $owner)
                <tr wire:key="owner-{{ $owner->id }}" data-owner-id="{{ $owner->id }}">
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="font-mono text-xs">{{ $owner->id }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">

                        <div class="font-medium"> {{ $owner->full_name1 }}
                            <span class="text-xs leading-5 text-gray-500">
                                [{{ $owner->language }}]
                            </span>
                        </div>
                        <div class="mt-1 text-xs leading-5 text-gray-500">
                            {{ $owner->coprop1_email }}
                        </div>
                        <div class="text-xs leading-5 text-gray-500">
                            {{ $owner->coprop1_phone }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="font-medium text-gray-900">{{ $owner->full_name2 }}
                        </div>

                        <div class="mt-1 text-xs leading-5 text-gray-500">
                            {{ $owner->coprop2_email }}
                        </div>
                        <div class="text-xs leading-5 text-gray-500">
                            {{ $owner->coprop2_phone }}</div>
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
                    <td class="px-6 py-4 text-sm text-gray-500"
                        data-owner-welcome="{{ $owner->id }}">
                        <div class="flex items-center gap-2">
                            <x-admin.status-indicator :active="$owner->welcome" />
                            <button type="button"
                                wire:click="confirmResendWelcomeMail({{ $owner->id }})"
                                title="{{ __('admin.owners.resend_welcome_email') }}"
                                data-action="resend-owner-welcome-{{ $owner->id }}"
                                class="rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#793d3d]">
                                <flux:icon.paper-airplane class="size-4" />
                            </button>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"
                        data-owner-terms-accepted="{{ $owner->id }}">
                        <x-admin.status-indicator :active="$owner->accepted_terms_at !== null" />
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions>
                            <x-admin.icon-button-edit
                                wire:click="openEditOwnerForm({{ $owner->id }})"
                                :title="__('admin.owners.edit_owner')" data-action="edit-owner-{{ $owner->id }}" />
                            <button type="button"
                                wire:click="toggleOwnerRow({{ $owner->id }})"
                                title="{{ __('admin.owners.view_properties') }}"
                                data-action="toggle-owner-inline-{{ $owner->id }}"
                                class="rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]">
                                <flux:icon.bars-3 class="size-4" />
                            </button>
                        </x-admin.table-row-actions>
                    </td>
                </tr>

                @if ($expandedOwnerId === $owner->id)
                    <tr wire:key="owner-inline-panel-{{ $owner->id }}" class="bg-gray-50">
                        <td colspan="10" class="px-6 py-4">
                            <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4"
                                data-owner-inline-panel="{{ $owner->id }}">
                                @if ($rowErrorMessage !== '')
                                    <div
                                        class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                        {{ $rowErrorMessage }}
                                    </div>
                                @endif

                                <x-admin.panel-table
                                    table-class="min-w-full divide-y divide-gray-200"
                                    data-owner-inline-table="{{ $owner->id }}">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.owners.location') }} /
                                                {{ __('admin.owners.property') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.locations.community_pct') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.locations.location_pct') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.owners.start_date') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.owners.end_date') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.owners.admin_validated') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                {{ __('admin.owners.owner_validated') }}
                                            </x-admin.table-header-cell>
                                            <x-admin.table-header-cell class="px-3 py-2">
                                                <span
                                                    class="sr-only">{{ __('general.buttons.save') }}</span>
                                            </x-admin.table-header-cell>
                                        </tr>
                                    </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($expandedAssignments as $assignment)
                <tr wire:key="owner-inline-assignment-{{ $assignment->id }}"
                    class="hover:bg-gray-50/60">
                    <td class="px-3 py-3 text-sm text-gray-600">
                        <div class="font-medium text-gray-900">
                            {{ $assignment->property->location->code }}
                        </div>
                        <div>
                            {{ $assignment->property->name }}
                        </div>
                    </td>
                    <td class="px-3 py-3 text-sm text-gray-600">
                        {{ $assignment->property->community_pct !== null ? number_format((float) $assignment->property->community_pct, 2, ',', '.') . '%' : '-' }}
                    </td>
                    <td class="px-3 py-3 text-sm text-gray-600">
                        {{ $assignment->property->location_pct !== null ? number_format((float) $assignment->property->location_pct, 2, ',', '.') . '%' : '-' }}
                    </td>
                    <td class="px-3 py-3">
                        <x-admin.form-date-input :label="__('admin.owners.start_date')" :model="'assignmentEdits.' . $assignment->id . '.start_date'"
                            :id="'assignment-start-date-' . $assignment->id" label-class="sr-only"
                            container-class="min-w-[8.5rem]" />
                    </td>
                    <td class="px-3 py-3">
                        <x-admin.form-date-input :label="__('admin.owners.end_date')" :model="'assignmentEdits.' . $assignment->id . '.end_date'"
                            :id="'assignment-end-date-' . $assignment->id" label-class="sr-only"
                            container-class="min-w-[8.5rem]" />
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]"
                            wire:model="assignmentEdits.{{ $assignment->id }}.admin_validated"
                            @disabled($assignment->end_date !== null)>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]"
                            wire:model="assignmentEdits.{{ $assignment->id }}.owner_validated"
                            @disabled($assignment->end_date !== null)>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <div class="flex justify-end">
                            <button type="button"
                                wire:click="saveAssignment({{ $assignment->id }})"
                                class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                {{ __('general.buttons.save') }}
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-3 py-3 text-sm text-gray-500">
                        {{ __('admin.owners.no_assignments') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <div class="mb-3 flex items-center justify-between gap-2">
            <flux:heading size="sm" class="text-zinc-800">
                {{ __('general.buttons.create_new') }}
            </flux:heading>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end"
            data-owner-inline-create="{{ $owner->id }}">
            <div class="md:col-span-5">
                <label for="inline-property-id" class="block text-sm font-medium text-stone-700">
                    {{ __('admin.owners.property') }}
                </label>
                <select id="inline-property-id" wire:model="inlinePropertyId"
                    class="mt-1 block w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                    <option value="">
                        {{ __('admin.owners.select_property') }}
                    </option>
                    @foreach ($assignableProperties as $property)
                        <option value="{{ $property->id }}">
                            {{ $property->location->code }} -
                            {{ __('admin.locations.types.' . $property->location->type) }}
                            - {{ $property->name }}
                        </option>
                    @endforeach
                </select>
                @error('inlinePropertyId')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-admin.form-date-input :label="__('admin.owners.start_date')" model="inlineStartDate"
                id="inline-start-date" container-class="md:col-span-3 min-w-[8.5rem]" />

            <x-admin.form-date-input :label="__('admin.owners.end_date')" model="inlineEndDate" id="inline-end-date"
                container-class="md:col-span-3 min-w-[8.5rem]" />

            <div class="md:col-span-1 md:flex md:justify-end">
                <button type="button" wire:click="createInlineAssignment"
                    class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    {{ __('general.buttons.create_new') }}
                </button>
            </div>
        </div>
    </div>
</div>
</td>
</tr>
@endif
@empty
<tr>
    <td colspan="10" class="px-6 py-8 text-center text-sm text-gray-500">
        {{ __('admin.owners.no_records') }}
    </td>
</tr>
@endforelse
</tbody>
</x-admin.panel-table>

<div class="mt-4" data-section="owners-pagination">
    {{ $owners->links() }}
</div>

@if ($showWelcomeModal)
    <dialog open
        class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
        aria-labelledby="welcome-modal-title">
        <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.876L6 12Zm0 0h7.5" />
                    </svg>
                </div>
                <div>
                    <h3 id="welcome-modal-title" class="text-base font-semibold text-gray-900">
                        {{ __('admin.owners.resend_welcome_email') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('admin.owners.confirm_resend_welcome') }}
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="cancelResendWelcomeMail"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                    {{ __('general.buttons.cancel') }}
                </button>
                <button type="button" wire:click="doResendWelcomeMail"
                    class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    {{ __('general.buttons.confirm') }}
                </button>
            </div>
        </div>
    </dialog>
@endif
</div>
