<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="w-full max-w-sm">
            <label for="messages-search" class="sr-only">{{ __('contact.admin.search') }}</label>
            <div class="relative">
                <span
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                    <flux:icon.magnifying-glass class="size-4" />
                </span>
                <input id="messages-search" type="text" wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('contact.admin.search') }}"
                    class="w-full rounded-md border border-stone-300 bg-white pl-10 pr-3 py-2 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
            </div>
        </div>

        <div class="flex items-center gap-2" data-messages-filter>
            <button type="button" wire:click="setReadFilter('read')"
                data-messages-filter-btn="read" @class([
                    'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                    'border-[#d9755b] bg-[#d9755b] text-white' => $readFilter === 'read',
                    'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                        $readFilter !== 'read',
                ])>
                {{ __('contact.admin.read') }}
            </button>
            <button type="button" wire:click="setReadFilter('unread')"
                data-messages-filter-btn="unread" @class([
                    'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                    'border-[#d9755b] bg-[#d9755b] text-white' => $readFilter === 'unread',
                    'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                        $readFilter !== 'unread',
                ])>
                {{ __('contact.admin.unread') }}
            </button>
            <button type="button" wire:click="setReadFilter('all')" data-messages-filter-btn="all"
                @class([
                    'rounded-full border px-3 py-1.5 text-sm font-semibold transition-colors',
                    'border-[#d9755b] bg-[#d9755b] text-white' => $readFilter === 'all',
                    'border-gray-300 bg-white text-gray-700 hover:border-[#d9755b] hover:text-[#793d3d]' =>
                        $readFilter !== 'all',
                ])>
                {{ __('general.buttons.all') }}
            </button>
        </div>
    </div>

    {{-- Messages table --}}
    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-[#793d3d]"
                    wire:click="sortBy('is_read')">
                    {{ __('contact.admin.read') }}
                    @if ($sortBy === 'is_read')
                        <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('contact.name') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('contact.email') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('contact.subject') }}
                </th>
                <th scope="col"
                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-[#793d3d]"
                    wire:click="sortBy('created_at')">
                    {{ __('contact.admin.received') }}
                    @if ($sortBy === 'created_at')
                        <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('general.buttons.delete') }}</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($messages as $msg)
                <tr wire:key="msg-{{ $msg->id }}"
                    class="{{ !$msg->is_read ? 'bg-[#edd2c7]/20 font-semibold' : 'bg-white' }} cursor-pointer hover:bg-gray-50"
                    wire:click="openMessage({{ $msg->id }})">
                    <td class="px-6 py-4 text-sm" wire:click.stop>
                        <button type="button"
                            wire:click="confirmReadToggle({{ $msg->id }}, {{ $msg->is_read ? 'false' : 'true' }})"
                            title="{{ $msg->is_read ? __('contact.admin.mark_unread') : __('contact.admin.mark_read') }}"
                            class="inline-flex min-w-16 items-center justify-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition-colors {{ $msg->is_read ? 'text-green-700 hover:border-green-300 hover:bg-green-100' : 'text-red-600 hover:border-red-300 hover:bg-red-100' }}">
                            @if ($msg->is_read)
                                <flux:icon.check-circle class="size-4" />
                            @else
                                <flux:icon.x-circle class="size-4" />
                            @endif
                        </button>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $msg->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $msg->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $msg->subject }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $msg->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium" wire:click.stop>
                        <div class="flex items-center justify-end gap-2">
                            {{-- Delete --}}
                            <button type="button" wire:click="confirmDelete({{ $msg->id }})"
                                title="{{ __('general.buttons.delete') }}"
                                class="rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-red-200 hover:bg-red-50 hover:text-[#d9755b]">
                                <flux:icon.trash class="size-4" />
                            </button>
                        </div>
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
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelReadToggle"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="doReadToggle"
                        class="rounded-md px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 {{ $readAction === 'read' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400' }}">
                        {{ __('general.buttons.confirm') }}
                    </button>
                </div>
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
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteMessage"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
