<div x-data="{
    focusForm() {
            const formCard = this.$root.querySelector('#admin-notice-form-card');
            if (formCard) {
                formCard.scrollTo({ top: 0, behavior: 'smooth' });
            }

            const titleInput = this.$root.querySelector('#titleEu');
            if (titleInput) {
                titleInput.focus({ preventScroll: true });
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
        <x-admin.side-panel-form section="notice-create-form" card-id="admin-notice-form-card"
            cancel-action="cancelForm">
            <form wire:submit="saveNotice" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <x-admin.bilingual-rich-text-tabs :title="__('notices.admin.title')" :locale-configs="$this->localeConfigsFor('title', 'notices.admin.title')"
                        mode="plain" :required-primary="true" />

                    <x-admin.bilingual-rich-text-tabs :title="__('notices.admin.content')" :locale-configs="$this->localeConfigsFor('content', 'notices.admin.content')"
                        :required-primary="true" />

                    <flux:field>
                        <flux:label>{{ __('notices.admin.tag') }}</flux:label>
                        <flux:select wire:model="selectedTagId" data-notice-tag-select>
                            <option value="">{{ __('notices.admin.tag_none') }}</option>
                            @foreach ($noticeTags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selectedTagId" />
                    </flux:field>

                    <x-admin.form-multi-checkbox-pills :legend="__('notices.admin.locations')" :options="$allLocations"
                        model="selectedLocations" value-key="code" label-key="label"
                        :empty-message="__('notices.admin.global_only')" />

                    <flux:field>
                        <flux:label>{{ __('notices.admin.documents') }}</flux:label>
                        <input type="file" wire:model="attachments" multiple
                            data-notice-documents-input
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700" />
                        <flux:error name="attachments.*" />
                        @if ($editingId)
                            <div class="mt-2">
                                <flux:button type="button" variant="primary"
                                    wire:click="uploadDocument({{ $editingId }})">
                                    {{ __('notices.admin.upload_documents') }}
                                </flux:button>
                            </div>
                        @endif
                    </flux:field>

                    @if ($storedDocuments !== [])
                        <div class="space-y-2" data-notice-documents-list>
                            @foreach ($storedDocuments as $document)
                                <div
                                    class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2">
                                    <div class="text-sm text-gray-700">
                                        <span>{{ $document['filename'] }}</span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            {{ __('notices.admin.downloads_count') }}:
                                            {{ $document['downloads_count'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <flux:button type="button" size="xs" variant="ghost"
                                            wire:click="toggleDocumentPublic({{ $document['id'] }})">
                                            {{ $document['is_public'] ? __('notices.admin.document_public') : __('notices.admin.document_private') }}
                                        </flux:button>
                                        <flux:button type="button" size="xs" variant="danger"
                                            wire:click="removeDocument({{ $document['id'] }})">
                                            {{ __('general.buttons.delete') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <x-admin.form-boolean-toggle :label="__('notices.admin.is_public')" model="isPublic"
                        :value="$isPublic" :true-label="__('admin.common.yes')" :false-label="__('admin.common.no')" />
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="(bool) $editingId"
                    cancel-action="cancelForm" />
            </form>
        </x-admin.side-panel-form>
    @endif

    {{-- Notices table --}}
    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>
                    {{ __('notices.admin.title') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell>
                    {{ __('notices.admin.tag') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell>
                    {{ __('notices.admin.locations') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell sortable wire:click="sortBy('is_public')">
                    {{ __('notices.admin.published_status') }}
                    @if ($sortColumn === 'is_public')
                        <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </x-admin.table-header-cell>

                <x-admin.table-header-cell sortable wire:click="sortBy('published_at')">
                    {{ __('notices.published_at') }}
                    @if ($sortColumn === 'published_at')
                        <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </x-admin.table-header-cell>

                <x-admin.table-header-cell>
                    {{ __('notices.admin.downloads_count') }}
                </x-admin.table-header-cell>

                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($notices as $notice)
                <tr wire:key="notice-row-{{ $notice->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $notice->title_eu }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if ($notice->tag)
                            <span
                                class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                {{ $notice->tag->name }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
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
                        <x-admin.action-link-confirm
                            wire:click="confirmPublish({{ $notice->id }}, {{ $notice->is_public ? 'false' : 'true' }})"
                            title="{{ $notice->is_public ? __('notices.admin.unpublish') : __('notices.admin.publish') }}"
                            :state="$notice->is_public ? 'success' : 'danger'">
                            @if ($notice->is_public)
                                <flux:icon.check-circle class="size-4" />
                            @else
                                <flux:icon.x-circle class="size-4" />
                            @endif
                        </x-admin.action-link-confirm>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $notice->published_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"
                        data-notice-download-count="{{ $notice->id }}">
                        {{ $notice->documents->sum('downloads_count') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions>
                            <x-admin.icon-button-edit
                                wire:click="editNotice({{ $notice->id }})" />
                            <x-admin.icon-button-delete
                                wire:click="confirmDelete({{ $notice->id }})" />
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
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
