<div>
    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <x-admin.filter-input id="users-search" :label="__('admin.users.search')" :placeholder="__('admin.users.search')"
            wire:model.live.debounce.300ms="search" />

        <div class="w-full max-w-xs">
            <label for="users-role-filter" class="sr-only">
                {{ __('admin.users.role_filter') }}
            </label>
            <select id="users-role-filter" wire:model.live="roleFilter"
                class="w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                <option value="all">{{ __('admin.users.role_filter_all') }}</option>
                @foreach ($roleOptions as $roleOption)
                    <option value="{{ $roleOption['value'] }}">{{ $roleOption['label'] }}</option>
                @endforeach
            </select>
        </div>

        @unless ($showForm)
            <x-admin.create-record-button wire:click="createUser" :label="__('admin.users.create')" />
        @endunless
    </div>

    @if ($showForm)
        <x-admin.side-panel-form section="users-create-form" card-id="admin-users-form-card"
            cancel-action="cancelForm">
            <form wire:submit="saveUser" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.form-input name="name" id="user-name" :label="__('admin.users.name')"
                        :disabled="$editingUserId !== null" :readonly="$editingUserId !== null" />

                    <x-admin.form-input name="email" id="user-email" type="email"
                        :label="__('admin.users.email')" :disabled="$editingUserId !== null" :readonly="$editingUserId !== null" />

                    <x-admin.form-boolean-toggle :label="__('admin.users.active')" model="isActive"
                        :value="$isActive" :true-label="__('admin.common.yes')" :false-label="__('admin.common.no')" />
                </div>

                @if ($editingUserId !== null && $editingOwnerId !== null)
                    <div class="rounded-md border border-[#edd2c7] bg-[#edd2c7]/20 p-3">
                        <a href="{{ route('admin.owners.index', ['editOwner' => $editingOwnerId]) }}"
                            class="inline-flex items-center rounded-md bg-[#793d3d] px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-[#5f2f2f]">
                            {{ __('admin.users.open_owner_profile') }}
                        </a>
                    </div>
                @endif

                <x-admin.form-multi-checkbox-pills :legend="__('admin.users.roles')" :options="$roleOptions"
                    model="selectedRoles" value-key="value" label-key="label" />
                @error('selectedRoles')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('selectedRoles.*')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                @if (in_array('admin_comunidad', $selectedRoles, true))
                    <x-admin.form-multi-checkbox-pills :legend="__('admin.users.managed_locations')" :options="$communityLocationOptions"
                        model="selectedManagedLocations" value-key="id" label-key="label" />
                    @error('selectedManagedLocations')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('selectedManagedLocations.*')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                @endif

                <x-admin.form-footer-actions show-default-buttons :is-editing="(bool) $editingUserId"
                    cancel-action="cancelForm" :save-label="__('general.buttons.save')" />

                @if ($editingUserId !== null)
                    <div class="rounded-xl border border-stone-200">
                        <div class="border-b border-stone-200 bg-stone-50 px-4 py-3">
                            <h3 class="text-sm font-semibold text-stone-800">
                                {{ __('admin.users.sessions_title') }}</h3>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <table class="min-w-full divide-y divide-stone-200">
                                <thead class="bg-stone-50">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-500">
                                            {{ __('admin.users.sessions_login_at') }}</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-500">
                                            {{ __('admin.users.sessions_logout_at') }}</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-500">
                                            {{ __('admin.users.sessions_ip') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100 bg-white">
                                    @forelse ($editingUserSessions as $session)
                                        <tr>
                                            <td class="px-4 py-2 text-xs text-stone-700">
                                                {{ $session['logged_in_at'] ?? '—' }}</td>
                                            <td class="px-4 py-2 text-xs text-stone-700">
                                                {{ $session['logged_out_at'] ?? '—' }}</td>
                                            <td class="px-4 py-2 text-xs text-stone-700">
                                                {{ $session['ip_address'] ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3"
                                                class="px-4 py-4 text-center text-xs text-stone-500">
                                                {{ __('admin.users.sessions_empty') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>
                    {{ __('admin.users.name') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.users.email') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.users.roles') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.users.delegated_vote_terms_accepted') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.users.managed_locations') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('admin.users.active') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($users as $user)
                <tr wire:key="admin-user-{{ $user->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <span
                                    class="inline-flex items-center rounded-full bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-700">
                                    {{ __('admin.users.roles_labels.' . $role->name) }}
                                </span>
                            @empty
                                <span class="text-stone-400">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @php($hasDelegatedVoteRole = $user->roles->contains('name', \App\Models\Role::DELEGATED_VOTE))
                        @if ($hasDelegatedVoteRole)
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->delegated_vote_terms_accepted_at !== null ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}"
                                data-user-delegated-terms="{{ $user->id }}">
                                {{ $user->delegated_vote_terms_accepted_at !== null ? __('admin.common.yes') : __('admin.common.no') }}
                            </span>
                        @else
                            <span class="text-stone-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->managedLocations as $managedLocation)
                                <span
                                    class="inline-flex items-center rounded-full bg-[#edd2c7]/45 px-2 py-0.5 text-xs font-medium text-[#793d3d]">
                                    {{ __('admin.locations.types.' . $managedLocation->type) }}
                                    {{ $managedLocation->code }}
                                </span>
                            @empty
                                <span class="text-stone-400">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $user->is_active ? __('admin.common.yes') : __('admin.common.no') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions>
                            @if (auth()->user()?->isSuperadmin())
                                <button type="button" wire:click="loginAs({{ $user->id }})"
                                    class="rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]"
                                    title="{{ __('admin.users.login_as') }}">
                                    <flux:icon.arrow-right-start-on-rectangle class="size-4" />
                                </button>
                            @endif

                            <x-admin.icon-button-edit wire:click="editUser({{ $user->id }})" />

                            <x-admin.icon-button-delete
                                wire:click="confirmDelete({{ $user->id }})" />
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('admin.users.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif

    @if ($confirmingDeleteUserId !== null)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="delete-user-modal-title">
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
                        <h3 id="delete-user-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('admin.users.delete_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('admin.users.delete_confirmation') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteUser"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
