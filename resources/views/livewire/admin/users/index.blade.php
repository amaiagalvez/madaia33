<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="w-full max-w-sm">
            <label for="users-search" class="sr-only">{{ __('admin.users.search') }}</label>
            <input id="users-search" type="text" wire:model.live.debounce.300ms="search"
                placeholder="{{ __('admin.users.search') }}"
                class="w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
        </div>

        @unless ($showForm)
            <button type="button" wire:click="createUser"
                class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                {{ __('admin.users.create') }}
            </button>
        @endunless
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40" data-section="users-create-form">
            <button type="button" wire:click="cancelForm"
                class="admin-slideover-backdrop absolute inset-0 bg-black/30"
                aria-label="{{ __('general.buttons.cancel') }}"></button>

            <div
                class="admin-slideover-panel absolute inset-y-0 right-0 z-50 h-full w-full max-w-3xl overflow-y-auto bg-white p-6 shadow-2xl">
                <x-admin.form-shell :title="$editingUserId ? __('general.buttons.edit') : __('admin.users.create')">
                    <form wire:submit="saveUser" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="user-name"
                                    class="mb-1 block text-sm font-medium text-stone-700">{{ __('validation.attributes.name') }}</label>
                                <input id="user-name" type="text" wire:model="name" @disabled($editingUserId !== null) @readonly($editingUserId !== null)
                                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="user-email"
                                    class="mb-1 block text-sm font-medium text-stone-700">{{ __('validation.attributes.email') }}</label>
                                <input id="user-email" type="email" wire:model="email" @disabled($editingUserId !== null) @readonly($editingUserId !== null)
                                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="user-active"
                                    class="mb-1 block text-sm font-medium text-stone-700">{{ __('admin.users.active') }}</label>
                                <select id="user-active" wire:model="isActive"
                                    class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                    <option value="1">{{ __('admin.common.yes') }}</option>
                                    <option value="0">{{ __('admin.common.no') }}</option>
                                </select>
                                @error('isActive')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @if ($editingUserId !== null && $editingOwnerId !== null)
                            <div class="rounded-md border border-[#edd2c7] bg-[#edd2c7]/20 p-3">
                                <a href="{{ route('admin.owners.show', ['owner' => $editingOwnerId]) }}"
                                    class="inline-flex items-center rounded-md bg-[#793d3d] px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-[#5f2f2f]">
                                    {{ __('admin.users.open_owner_profile') }}
                                </a>
                            </div>
                        @endif

                        <div>
                            <p class="mb-2 text-sm font-medium text-stone-700">
                                {{ __('admin.users.roles') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($roles as $role)
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" wire:model="selectedRoles"
                                            value="{{ $role }}" class="sr-only peer" />
                                        <span
                                            class="inline-flex items-center rounded-full border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 transition-colors peer-checked:border-[#d9755b] peer-checked:bg-[#d9755b] peer-checked:text-white hover:border-[#d9755b]/50 hover:bg-[#edd2c7]/20">
                                            {{ __('admin.users.roles_labels.' . $role) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedRoles')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @error('selectedRoles.*')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (in_array('admin_comunidad', $selectedRoles, true))
                            <div>
                                <p class="mb-2 text-sm font-medium text-stone-700">
                                    {{ __('admin.users.managed_locations') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($communityLocations as $location)
                                        <label class="cursor-pointer select-none">
                                            <input type="checkbox"
                                                wire:model="selectedManagedLocations"
                                                value="{{ $location->id }}" class="sr-only peer" />
                                            <span
                                                class="inline-flex items-center rounded-full border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 transition-colors peer-checked:border-[#d9755b] peer-checked:bg-[#d9755b] peer-checked:text-white hover:border-[#d9755b]/50 hover:bg-[#edd2c7]/20">
                                                {{ __('admin.locations.types.' . $location->type) }}
                                                {{ $location->code }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedManagedLocations')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @error('selectedManagedLocations.*')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div
                            class="mt-6 flex items-center justify-end gap-2 border-t border-stone-200 pt-4">
                            <button type="button" wire:click="cancelForm"
                                class="inline-flex items-center rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 shadow-sm hover:bg-stone-50">
                                {{ __('general.buttons.cancel') }}
                            </button>
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                {{ __('general.buttons.save') }}
                            </button>
                        </div>

                        @if ($editingUserId !== null)
                            <div class="rounded-xl border border-stone-200">
                                <div class="border-b border-stone-200 bg-stone-50 px-4 py-3">
                                    <h3 class="text-sm font-semibold text-stone-800">{{ __('admin.users.sessions_title') }}</h3>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-stone-200">
                                        <thead class="bg-stone-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-500">{{ __('admin.users.sessions_login_at') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-500">{{ __('admin.users.sessions_logout_at') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-500">IP</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-stone-100 bg-white">
                                            @forelse ($editingUserSessions as $session)
                                                <tr>
                                                    <td class="px-4 py-2 text-xs text-stone-700">{{ $session['logged_in_at'] ?? '—' }}</td>
                                                    <td class="px-4 py-2 text-xs text-stone-700">{{ $session['logged_out_at'] ?? '—' }}</td>
                                                    <td class="px-4 py-2 text-xs text-stone-700">{{ $session['ip_address'] ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-4 py-4 text-center text-xs text-stone-500">{{ __('admin.users.sessions_empty') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </form>
                </x-admin.form-shell>
            </div>
        </div>
    @endif

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('validation.attributes.name') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('validation.attributes.email') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.users.roles') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.users.managed_locations') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('admin.users.active') }}
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </th>
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
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->managedLocations as $managedLocation)
                                <span
                                    class="inline-flex items-center rounded-full bg-[#edd2c7]/45 px-2 py-0.5 text-xs font-medium text-[#793d3d]">
                                    {{ __('admin.locations.types.' . $managedLocation->type) }} {{ $managedLocation->code }}
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
                        <div class="flex items-center justify-end gap-2">
                            @if (auth()->user()?->isSuperadmin())
                                <button type="button" wire:click="loginAs({{ $user->id }})"
                                    class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                                    title="{{ __('admin.users.login_as') }}">
                                    <flux:icon.arrow-right-start-on-rectangle class="size-4" />
                                </button>
                            @endif

                            <button type="button" wire:click="editUser({{ $user->id }})"
                                class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]"
                                title="{{ __('general.buttons.edit') }}">
                                <flux:icon.pencil-square class="size-4" />
                            </button>

                            <button type="button"
                                wire:click="confirmDelete({{ $user->id }})"
                                class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                                title="{{ __('general.buttons.delete') }}">
                                <flux:icon.trash class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
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
                <h3 id="delete-user-modal-title" class="text-base font-semibold text-gray-900">
                    {{ __('admin.users.delete_title') }}
                </h3>
                <p class="text-sm text-gray-600">{{ __('admin.users.delete_confirmation') }}</p>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteUser"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
