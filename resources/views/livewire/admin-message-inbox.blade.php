<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700">{{ __('contact.admin.inbox') }}</h2>
    </div>

    {{-- Messages table --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
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
                        class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700"
                        wire:click="sortBy('created_at')">
                        {{ __('contact.admin.received') }}
                        @if ($sortBy === 'created_at')
                            <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col"
                        class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700"
                        wire:click="sortBy('is_read')">
                        {{ __('contact.admin.read') }}
                        @if ($sortBy === 'is_read')
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
                        class="{{ !$msg->is_read ? 'bg-blue-50 font-semibold' : 'bg-white' }} cursor-pointer hover:bg-gray-50"
                        wire:click="openMessage({{ $msg->id }})">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $msg->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $msg->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $msg->subject }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $msg->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($msg->is_read)
                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    {{ __('contact.admin.read') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                    {{ __('contact.admin.unread') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium" wire:click.stop>
                            <div class="flex items-center justify-end gap-2">
                                {{-- Toggle read/unread --}}
                                <button wire:click="toggleRead({{ $msg->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    {{ $msg->is_read ? __('contact.admin.mark_unread') : __('contact.admin.mark_read') }}
                                </button>

                                {{-- Delete with confirmation --}}
                                @if ($confirmingDeleteId === $msg->id)
                                    <span class="text-sm text-gray-600">
                                        {{ __('contact.admin.confirm_delete') }}
                                    </span>
                                    <button wire:click="deleteMessage" class="text-red-600 hover:text-red-900">
                                        {{ __('general.buttons.delete') }}
                                    </button>
                                    <button wire:click="cancelDelete" class="text-gray-500 hover:text-gray-700">
                                        {{ __('general.buttons.cancel') }}
                                    </button>
                                @else
                                    <button wire:click="confirmDelete({{ $msg->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        {{ __('general.buttons.delete') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Expanded detail row --}}
                    @if ($openMessageId === $msg->id)
                        <tr wire:key="detail-{{ $msg->id }}" class="bg-gray-50">
                            <td colspan="6" class="px-6 py-4">
                                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                    <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-xs font-medium uppercase text-gray-500">
                                                {{ __('contact.admin.from') }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ $msg->name }} &lt;{{ $msg->email }}&gt;
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium uppercase text-gray-500">
                                                {{ __('contact.subject') }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $msg->subject }}</dd>
                                        </div>
                                    </dl>
                                    <div class="mt-4">
                                        <dt class="text-xs font-medium uppercase text-gray-500">
                                            {{ __('contact.message') }}
                                        </dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-sm text-gray-800">
                                            {{ $msg->message }}
                                        </dd>
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
        </table>
    </div>

    {{-- Delete confirmation modal --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="cancelDelete"
            role="dialog" aria-modal="true" aria-labelledby="delete-confirm-msg">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <p id="delete-confirm-msg" class="text-sm text-gray-700">{{ __('contact.admin.confirm_delete') }}</p>
                <div class="mt-4 flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button wire:click="deleteMessage"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
