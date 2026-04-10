<div x-data="{
    focusForm() {
            const formCard = this.$root.querySelector('#admin-notice-form-card');
            if (formCard) {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            const titleInput = this.$root.querySelector('#titleEu');
            if (titleInput) {
                titleInput.focus();
            }
        },
        format(field, command) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            editor.focus();
            document.execCommand(command, false, null);
            this.sync(field);
        },
        link(field) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            const url = window.prompt('{{ __('admin.settings_form.editor_link_prompt') }}', 'https://');
            if (!url) {
                return;
            }

            editor.focus();
            document.execCommand('createLink', false, url);
            this.sync(field);
        },
        sync(field) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            const html = editor.innerHTML.trim();
            this.$wire.set(field, html === '<br>' ? '' : html);
        },
}" x-on:admin-notice-form-focus.window="focusForm()">
    {{-- Flash message --}}
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-end">
        @unless ($showForm)
            <x-admin.create-record-button wire:click="createNotice" />
        @endunless
    </div>

    {{-- Right panel form --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40" data-section="notice-create-form">
            <button type="button" wire:click="cancelForm"
                class="admin-slideover-backdrop absolute inset-0 bg-black/30"
                aria-label="{{ __('general.buttons.cancel') }}"></button>

            <div id="admin-notice-form-card"
                class="admin-slideover-panel absolute inset-y-0 right-0 z-50 h-full w-full max-w-4xl overflow-y-auto bg-white p-6 shadow-2xl">
                <form wire:submit="saveNotice" novalidate>
                    <div class="grid grid-cols-1 gap-4">
                        <x-admin.bilingual-rich-text-tabs :title="__('notices.admin.title')" :locale-configs="$this->localeConfigsFor('title', 'notices.admin.title')"
                            mode="plain" :required-primary="true" />

                        <x-admin.bilingual-rich-text-tabs :title="__('notices.admin.content')" :locale-configs="$this->localeConfigsFor('content', 'notices.admin.content')"
                            :required-primary="true" />

                        {{-- Locations --}}
                        @if ($allLocations !== [])
                            <div>
                                <fieldset>
                                    <legend class="block text-sm font-medium text-gray-700">
                                        {{ __('notices.admin.locations') }}
                                    </legend>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($allLocations as $loc)
                                            <label class="cursor-pointer select-none">
                                                <input type="checkbox"
                                                    wire:model="selectedLocations"
                                                    value="{{ $loc['code'] }}"
                                                    class="sr-only peer" />
                                                <span
                                                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors peer-checked:bg-[#d9755b] peer-checked:text-white peer-checked:border-[#d9755b] border-gray-300 text-gray-600 hover:border-[#d9755b]/50 hover:bg-[#edd2c7]/20">
                                                    {{ $loc['label'] }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>
                        @else
                            <p
                                class="rounded-md border border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-600">
                                {{ __('notices.admin.global_only') }}
                            </p>
                        @endif

                        {{-- Is public toggle --}}
                        <div>
                            <label for="isPublic"
                                class="mb-2 block text-sm font-semibold text-stone-800">
                                {{ __('notices.admin.is_public') }}
                            </label>
                            <label for="isPublic"
                                class="flex cursor-pointer items-center justify-between rounded-2xl border border-brand-300/50 bg-brand-100/30 px-4 py-3 transition-colors hover:border-brand-600/50 hover:bg-brand-100/50">
                                <div>
                                    <p class="text-sm font-semibold text-brand-900">
                                        {{ __('notices.admin.is_public') }}</p>
                                    <p class="text-xs text-stone-600">
                                        {{ $isPublic ? __('notices.admin.publish') : __('notices.admin.unpublish') }}
                                    </p>
                                </div>
                                <span
                                    class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors {{ $isPublic ? 'bg-brand-600' : 'bg-stone-300' }}">
                                    <span
                                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform {{ $isPublic ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </span>
                                <input id="isPublic" type="checkbox" wire:model.live="isPublic"
                                    class="sr-only" />
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                            {{ $editingId ? __('general.buttons.save') : __('general.buttons.create_new') }}
                        </button>
                        <button type="button" wire:click="cancelForm"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                            {{ __('general.buttons.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Notices table --}}
    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('notices.admin.title_eu') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('notices.admin.locations') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('notices.admin.published_status') }}
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('notices.published_at') }}
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($notices as $notice)
                <tr wire:key="notice-row-{{ $notice->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $notice->title_eu }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if ($notice->locations->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($notice->locations as $loc)
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                        {{ $loc->location_code }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <button type="button"
                            wire:click="confirmPublish({{ $notice->id }}, {{ $notice->is_public ? 'false' : 'true' }})"
                            title="{{ $notice->is_public ? __('notices.admin.unpublish') : __('notices.admin.publish') }}"
                            class="inline-flex min-w-28 items-center justify-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors {{ $notice->is_public ? 'border-green-200 bg-green-50 text-green-700 hover:border-green-300 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-600 hover:border-red-300 hover:bg-red-100' }}">
                            @if ($notice->is_public)
                                <flux:icon.check-circle class="size-4" />
                            @else
                                <flux:icon.x-circle class="size-4" />
                            @endif
                            <span>{{ $notice->is_public ? __('notices.admin.published_status') : __('notices.admin.is_public') }}</span>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $notice->published_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Edit --}}
                            <button type="button" wire:click="editNotice({{ $notice->id }})"
                                title="{{ __('general.buttons.edit') }}"
                                class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]">
                                <flux:icon.pencil-square class="size-4" />
                            </button>

                            {{-- Delete --}}
                            <button type="button" wire:click="confirmDelete({{ $notice->id }})"
                                title="{{ __('general.buttons.delete') }}"
                                class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-500">
                                <flux:icon.trash class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('notices.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($notices->hasPages())
        <div class="mt-6">
            {{ $notices->links() }}
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal)
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
                            {{ __('notices.admin.delete_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('notices.admin.confirm_delete') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteNotice"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif

    {{-- Publish confirmation modal --}}
    @if ($showPublishModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="publish-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $publishAction === 'publish' ? 'bg-green-100' : 'bg-amber-100' }}">
                        @if ($publishAction === 'publish')
                            <svg class="h-5 w-5 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-amber-600" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 id="publish-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ $publishAction === 'publish' ? __('notices.admin.publish') : __('notices.admin.unpublish') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $publishAction === 'publish' ? __('notices.admin.confirm_publish') : __('notices.admin.confirm_unpublish') }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelPublish"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="doPublish"
                        class="rounded-md px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 {{ $publishAction === 'publish' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400' }}">
                        {{ __('general.buttons.confirm') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
