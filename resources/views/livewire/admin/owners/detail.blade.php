<div>
    @if ($errorMessage !== '')
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            data-message="error">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <flux:heading size="xl">{{ $owner->coprop1_name }}</flux:heading>
        <div class="flex gap-2">
            <flux:button variant="ghost" wire:click="$set('showEditForm', true)">
                {{ __('admin.owners.edit_data') }}</flux:button>
            <flux:button variant="danger" wire:click="deactivateOwner" data-action="deactivate-owner">
                {{ __('admin.owners.deactivate') }}
            </flux:button>
        </div>
    </div>

    @if ($showEditForm)
        <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
            data-section="owner-edit-form">
            <div class="grid gap-4 md:grid-cols-2">
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
                    <flux:label>{{ __('admin.owners.form.coprop2_phone') }}</flux:label>
                    <flux:input wire:model="coprop2Phone" />
                    <flux:error name="coprop2Phone" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop2_email') }}</flux:label>
                    <flux:input wire:model="coprop2Email" type="email" />
                    <flux:error name="coprop2Email" />
                </flux:field>
            </div>
            <div class="mt-4 flex gap-2">
                <flux:button variant="primary" wire:click="saveOwner">{{ __('general.buttons.save') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="cancelEdit">{{ __('general.buttons.cancel') }}
                </flux:button>
            </div>
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('admin.owners.assignments') }}</flux:heading>
        <flux:button variant="primary" wire:click="$set('showAssignForm', true)">
            {{ __('admin.owners.add_property') }}</flux:button>
    </div>

    @if ($showAssignForm)
        <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
            data-section="assign-form">
            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('admin.owners.property') }}</flux:label>
                    <flux:select wire:model="assignPropertyId">
                        <flux:select.option value="">{{ __('admin.owners.select_property') }}
                        </flux:select.option>
                        @foreach ($availableProperties as $property)
                            <flux:select.option :value="$property->id">
                                {{ $property->location->code }} {{ $property->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="assignPropertyId" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin.owners.start_date') }}</flux:label>
                    <flux:input type="date" wire:model="assignStartDate" />
                    <flux:error name="assignStartDate" />
                </flux:field>
            </div>
            <div class="mt-4 flex gap-2">
                <flux:button variant="primary" wire:click="assignProperty">{{ __('admin.owners.assign') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="$set('showAssignForm', false)">
                    {{ __('general.buttons.cancel') }}</flux:button>
            </div>
        </div>
    @endif

    <div
        class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="min-w-full text-sm">
            <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr class="text-left text-zinc-600 dark:text-zinc-300">
                    <th class="px-4 py-3">{{ __('admin.owners.location') }}</th>
                    <th class="px-4 py-3">{{ __('admin.owners.property') }}</th>
                    <th class="px-4 py-3">{{ __('admin.owners.start_date') }}</th>
                    <th class="px-4 py-3">{{ __('admin.owners.end_date') }}</th>
                    <th class="px-4 py-3">{{ __('admin.owners.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr wire:key="assignment-{{ $assignment->id }}"
                        data-assignment-id="{{ $assignment->id }}"
                        class="border-b border-zinc-100 dark:border-zinc-700">
                        <td class="px-4 py-3">{{ $assignment->property->location->code }}</td>
                        <td class="px-4 py-3">{{ $assignment->property->name }}</td>
                        <td class="px-4 py-3">
                            {{ optional($assignment->start_date)->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            @if ($unassigningId === $assignment->id)
                                <div class="flex items-center gap-2">
                                    <flux:input type="date" wire:model="unassignEndDate"
                                        size="sm" />
                                    <flux:button variant="primary" size="sm"
                                        wire:click="confirmUnassign">{{ __('general.buttons.confirm') }}
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm"
                                        wire:click="cancelUnassign">{{ __('general.buttons.cancel') }}
                                    </flux:button>
                                </div>
                            @else
                                {{ optional($assignment->end_date)->format('Y-m-d') ?? '—' }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$assignment->isActive() ? 'green' : 'zinc'">
                                {{ $assignment->isActive() ? __('admin.owners.active') : __('admin.owners.closed') }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($assignment->isActive() && $unassigningId !== $assignment->id)
                                <flux:button variant="ghost" size="sm"
                                    wire:click="startUnassign({{ $assignment->id }})">
                                    {{ __('admin.owners.unassign') }}
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-400">
                            {{ __('admin.owners.no_assignments') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
