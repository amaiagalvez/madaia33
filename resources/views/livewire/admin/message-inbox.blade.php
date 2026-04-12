<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <x-admin.filter-input id="messages-search" :label="__('contact.admin.search')" :placeholder="__('contact.admin.search')"
            wire:model.live.debounce.300ms="search" />

        <x-admin.filter-toggle-group data-messages-filter>
            <x-admin.filter-toggle-button wire:click="setReadFilter('read')"
                data-messages-filter-btn="read" key="read" :active="$readFilter === 'read'">
                {{ __('contact.admin.read') }}
            </x-admin.filter-toggle-button>

            <x-admin.filter-toggle-button wire:click="setReadFilter('unread')"
                data-messages-filter-btn="unread" key="unread" :active="$readFilter === 'unread'">
                {{ __('contact.admin.unread') }}
            </x-admin.filter-toggle-button>

            <x-admin.filter-toggle-button wire:click="setReadFilter('all')"
                data-messages-filter-btn="all" key="all" :active="$readFilter === 'all'">
                {{ __('general.buttons.all') }}
            </x-admin.filter-toggle-button>
        </x-admin.filter-toggle-group>
    </div>

    {{-- Messages table --}}
    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell sortable wire:click="sortBy('is_read')">
                    {{ __('contact.admin.read') }}
                    @if ($sortColumn === 'is_read')
                        <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </x-admin.table-header-cell>

                <x-admin.table-header-cell>
                    {{ __('contact.name') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell>
                    {{ __('contact.email') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell>
                    {{ __('contact.subject') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell sortable wire:click="sortBy('created_at')">
                    {{ __('contact.admin.received') }}
                    @if ($sortColumn === 'created_at')
                        <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </x-admin.table-header-cell>

                <x-admin.table-header-cell class="relative">
                    @if ($canDeleteMessages)
                        <span class="sr-only">{{ __('general.buttons.delete') }}</span>
                    @endif
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($messages as $msg)
                <tr wire:key="msg-{{ $msg->id }}"
                    class="{{ !$msg->is_read ? 'bg-[#edd2c7]/20 font-semibold' : 'bg-white' }} cursor-pointer hover:bg-gray-50"
                    wire:click="openMessage({{ $msg->id }})">
                    <td class="px-6 py-4 text-sm" wire:click.stop>
                        <x-admin.action-link-confirm
                            wire:click="confirmReadToggle({{ $msg->id }}, {{ $msg->is_read ? 'false' : 'true' }})"
                            title="{{ $msg->is_read ? __('contact.admin.mark_unread') : __('contact.admin.mark_read') }}"
                            :state="$msg->is_read ? 'success' : 'danger'">
                            @if ($msg->is_read)
                                <flux:icon.check-circle class="size-4" />
                            @else
                                <flux:icon.x-circle class="size-4" />
                            @endif
                        </x-admin.action-link-confirm>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $msg->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $msg->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $msg->subject }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $msg->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium" wire:click.stop>
                        @if ($canDeleteMessages)
                            <x-admin.table-row-actions>
                                <x-admin.icon-button-delete
                                    wire:click="confirmDelete({{ $msg->id }})" />
                            </x-admin.table-row-actions>
                        @endif
                    </td>
                </tr>

                {{-- Expanded detail row --}}
                @if ($openMessageId === $msg->id)
                    <tr wire:key="detail-{{ $msg->id }}" class="bg-gray-50">
                        <td colspan="6" class="px-6 py-4">
                            <div
                                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                <div class="border-b border-gray-200 bg-white px-4 py-4 sm:px-5">
                                    <div
                                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <span
                                                class="mt-1 truncate text-base font-semibold text-gray-900">
                                                {{ $msg->subject }}
                                            </span>
                                        </div>
                                        <p
                                            class="shrink-0 text-xs font-medium uppercase tracking-wide text-gray-500">
                                            {{ $msg->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>

                                    <div class="mt-4 flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#edd2c7] text-sm font-semibold text-[#793d3d]">
                                            {{ strtoupper(substr($msg->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-gray-900">
                                                {{ $msg->name }}
                                            </p>
                                            <p class="truncate text-xs text-gray-500">
                                                {{ $msg->email }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/80 px-3 py-3 sm:px-4">
                                    <div
                                        class="whitespace-pre-line rounded-lg border border-gray-200 bg-white px-3 py-3 text-left text-sm leading-relaxed text-gray-800 sm:px-4">
                                        {{ trim($msg->message) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('contact.admin.inbox') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($messages->hasPages())
        <div class="mt-6">
            {{ $messages->links() }}
        </div>
    @endif

    {{-- Read-status confirmation modal --}}
    @if ($showReadModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="read-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $readAction === 'read' ? 'bg-green-100' : 'bg-amber-100' }}">
                        @if ($readAction === 'read')
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
                        <h3 id="read-modal-title" class="text-base font-semibold text-gray-900">
                            {{ $readAction === 'read' ? __('contact.admin.mark_read') : __('contact.admin.mark_unread') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $readAction === 'read' ? __('contact.admin.confirm_mark_read') : __('contact.admin.confirm_mark_unread') }}
                        </p>
                    </div>
                </div>
                <x-admin.form-footer-actions class="mt-0 justify-end">
                    <button type="button" wire:click="cancelReadToggle"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="doReadToggle"
                        class="rounded-md px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 {{ $readAction === 'read' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400' }}">
                        {{ __('general.buttons.confirm') }}
                    </button>
                </x-admin.form-footer-actions>
            </div>
        </dialog>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal && $confirmingDeleteId)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="delete-modal-title">
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
                        <h3 id="delete-modal-title" class="text-base font-semibold text-gray-900">
                            {{ __('contact.admin.delete_title') ?? __('general.buttons.delete') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('contact.admin.confirm_delete') }}</p>
                    </div>
                </div>
                <x-admin.form-footer-actions class="mt-0 justify-end">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteMessage"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </x-admin.form-footer-actions>
            </div>
        </dialog>
    @endif
</div>
