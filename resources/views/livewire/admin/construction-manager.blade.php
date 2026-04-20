<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-end">
        @unless ($showForm)
            <x-admin.create-record-button wire:click="createConstruction" />
        @endunless
    </div>

    @if ($showForm)
        <x-admin.side-panel-form section="construction-create-form"
            card-id="admin-construction-form-card" cancel-action="cancelForm">
            <form wire:submit="saveConstruction" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin.constructions.fields.title') }}</flux:label>
                        <flux:input wire:model="title" />
                        <flux:error name="title" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin.constructions.fields.description') }}</flux:label>
                        <flux:textarea wire:model="description" rows="4" />
                        <flux:error name="description" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.form-date-input :label="__('admin.constructions.fields.starts_at')" model="startsAt" />
                        <x-admin.form-date-input :label="__('admin.constructions.fields.ends_at')" model="endsAt" />
                    </div>

                    <x-admin.form-boolean-toggle :label="__('admin.constructions.fields.is_active')" model="isActive"
                        :value="$isActive" :true-label="__('admin.common.yes')" :false-label="__('admin.common.no')" />

                    @if ($canAssignManagers)
                        <x-admin.form-multi-checkbox-pills :legend="__('admin.constructions.fields.managers')" :options="$managerOptions
                            ->map(
                                fn($user): array => [
                                    'id' => (string) $user->id,
                                    'label' => $user->name,
                                ],
                            )
                            ->values()
                            ->all()"
                            model="selectedManagers" value-key="id" label-key="label" />
                    @endif
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="(bool) $editingConstructionId"
                    cancel-action="cancelForm" />
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>{{ __('admin.constructions.fields.title') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('admin.constructions.fields.starts_at') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('admin.constructions.fields.ends_at') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('admin.constructions.fields.is_active') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('admin.constructions.fields.managers_count') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative"><span
                        class="sr-only">Actions</span></x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($constructions as $construction)
                <tr wire:key="construction-row-{{ $construction->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $construction->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $construction->starts_at?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $construction->ends_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm">
                        <x-admin.action-link-confirm
                            wire:click="confirmToggleActive({{ $construction->id }})"
                            title="{{ $construction->is_active ? __('admin.constructions.actions.deactivate') : __('admin.constructions.actions.activate') }}"
                            :state="$construction->is_active ? 'success' : 'danger'">
                            @if ($construction->is_active)
                                <flux:icon.check-circle class="size-4" />
                            @else
                                <flux:icon.x-circle class="size-4" />
                            @endif
                        </x-admin.action-link-confirm>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $construction->managers_count }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions>
                            <x-admin.icon-button-edit
                                wire:click="editConstruction({{ $construction->id }})" />
                            @if ($canDeleteConstruction)
                                <x-admin.icon-button-delete
                                    wire:click="confirmDelete({{ $construction->id }})" />
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('admin.constructions.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($constructions->hasPages())
        <div class="mt-6">
            {{ $constructions->links() }}
        </div>
    @endif

    @if ($confirmingDeleteId !== null)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-gray-900">
                    {{ __('admin.constructions.delete_title') }}</h3>
                <p class="text-sm text-gray-600">{{ __('admin.constructions.confirm_delete') }}</p>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteConstruction"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif

    @if ($showActiveModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="construction-active-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $activeAction === 'activate' ? 'bg-green-100' : 'bg-amber-100' }}">
                        @if ($activeAction === 'activate')
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 id="construction-active-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ $activeAction === 'activate' ? __('admin.constructions.actions.activate') : __('admin.constructions.actions.deactivate') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $activeAction === 'activate' ? __('admin.constructions.confirm_activate') : __('admin.constructions.confirm_deactivate') }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelToggleActive"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-600">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="doToggleActive"
                        class="rounded-md px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 {{ $activeAction === 'activate' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400' }}">
                        {{ __('general.buttons.confirm') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
