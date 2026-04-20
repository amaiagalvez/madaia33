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

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <x-admin.filter-input id="notices-search" :label="__('notices.admin.search')" :placeholder="__('notices.admin.search')"
            wire:model.live.debounce.300ms="search" data-notice-search />

        <x-admin.filter-toggle-group data-notice-tag-filters>
            <x-admin.filter-toggle-button wire:click="setTagFilter('all')"
                data-notice-tag-filter="all" key="all" :active="$tagFilter === 'all'">
                {{ __('general.buttons.all') }}
            </x-admin.filter-toggle-button>

            <x-admin.filter-toggle-button wire:click="setTagFilter('untagged')"
                data-notice-tag-filter="untagged" key="untagged" :active="$tagFilter === 'untagged'">
                {{ __('notices.admin.filter_without_tag') }}
            </x-admin.filter-toggle-button>

            @foreach ($noticeTags as $tag)
                <x-admin.filter-toggle-button wire:click="setTagFilter('{{ $tag->id }}')"
                    data-notice-tag-filter="{{ $tag->id }}" key="tag-{{ $tag->id }}"
                    :active="$tagFilter === (string) $tag->id">
                    {{ $tag->name }}
                </x-admin.filter-toggle-button>
            @endforeach
        </x-admin.filter-toggle-group>
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
                        model="selectedLocations" value-key="id" label-key="label"
                        :empty-message="__('notices.admin.global_only')" />

                    <x-admin.form-file-input id="noticeAttachments" model="attachments" multiple
                        :label="__('notices.admin.documents')" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png"
                        :hint="$editingId
                            ? __('notices.admin.documents_auto_upload_hint')
                            : __('notices.admin.documents_pending_hint')" data-notice-documents-input data-auto-upload="true" />

                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($storedDocuments !== [] || $attachments !== [])
                        <div class="space-y-4 rounded-xl border border-stone-200 bg-stone-50 p-4"
                            data-notice-documents-panel>
                            @if ($storedDocuments !== [])
                                <div data-notice-documents-list>
                                    <p class="text-sm font-semibold text-stone-900">
                                        {{ __('notices.admin.uploaded_documents') }}
                                    </p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach ($storedDocuments as $document)
                                            <li
                                                class="flex items-center justify-between rounded-lg border border-stone-200 bg-white px-3 py-2">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm text-stone-700">
                                                        {{ $document['filename'] }}</p>
                                                    <p class="text-xs text-stone-500">
                                                        {{ __('notices.admin.downloads_count') }}:
                                                        {{ $document['downloads_count'] }}
                                                    </p>
                                                </div>
                                                <div class="ml-3 flex items-center gap-2">
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-full border border-stone-300 bg-white px-2.5 py-1 text-xs font-semibold text-stone-700 transition hover:bg-stone-100"
                                                        wire:click="toggleDocumentPublic({{ $document['id'] }})">
                                                        {{ $document['is_public'] ? __('notices.admin.document_public') : __('notices.admin.document_private') }}
                                                    </button>
                                                    <x-admin.icon-button-delete
                                                        wire:click="removeDocument({{ $document['id'] }})"
                                                        title="{{ __('general.buttons.delete') }}" />
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if ($attachments !== [])
                                <div data-notice-pending-documents>
                                    <p class="text-sm font-semibold text-stone-900">
                                        {{ __('notices.admin.pending_documents') }}
                                    </p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach ($attachments as $index => $attachment)
                                            <li
                                                class="flex items-center justify-between rounded-lg border border-dashed border-brand-600/40 bg-white px-3 py-2">
                                                <span
                                                    class="text-sm text-stone-700">{{ $attachment->getClientOriginalName() }}</span>
                                                <x-admin.icon-button-delete
                                                    wire:click="removePendingAttachment({{ $index }})"
                                                    title="{{ __('general.buttons.delete') }}" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.form-date-input :label="__('notices.published_at')" model="publishedAt" />

                        <x-admin.form-boolean-toggle :label="__('notices.admin.is_public')" model="isPublic"
                            :value="$isPublic" :true-label="__('admin.common.yes')" :false-label="__('admin.common.no')" />
                    </div>
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
                            @if ($notice->tag?->construction !== null)
                                <button type="button"
                                    wire:click="showReaders({{ $notice->id }})"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                                    title="{{ __('notices.admin.show_readers') }}"
                                    data-notice-readers-btn="{{ $notice->id }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                    </svg>
                                </button>
                            @endif
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
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-600">
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
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-600">
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

    {{-- Readers modal --}}
    @if ($showReadersModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="readers-modal-title" data-notice-readers-modal>
            <div class="fixed inset-0 bg-black/40" wire:click="closeReadersModal"
                aria-hidden="true"></div>
            <div
                class="relative mx-4 w-full max-w-lg space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <h3 id="readers-modal-title" class="text-base font-semibold text-gray-900">
                        {{ __('notices.admin.readers_title') }}
                    </h3>
                    <button type="button" wire:click="closeReadersModal"
                        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        aria-label="{{ __('general.close') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if (count($noticeReaders) === 0)
                    <p class="text-sm text-gray-500" data-notice-readers-empty>
                        {{ __('notices.admin.readers_empty') }}
                    </p>
                @else
                    <div class="max-h-80 overflow-y-auto" data-notice-readers-list>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600">
                                        {{ __('notices.admin.readers_status') }}
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600">
                                        {{ __('admin.owners.title') }}
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600">
                                        {{ __('notices.admin.readers_opened_at') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($noticeReaders as $reader)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                                'bg-emerald-100 text-emerald-700' => $reader['has_opened'],
                                                'bg-red-100 text-red-700' => !$reader['has_opened'],
                                            ])
                                                data-notice-reader-status>
                                                {{ $reader['has_opened'] ? __('notices.admin.readers_opened') : __('notices.admin.readers_unopened') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-900">
                                            {{ $reader['owner_name'] }}</td>
                                        <td class="px-4 py-2 text-gray-500">
                                            {{ $reader['has_opened'] ? $reader['opened_at'] : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="flex justify-end">
                    <button type="button" wire:click="closeReadersModal"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-600">
                        {{ __('general.buttons.close') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
