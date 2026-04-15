<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        @unless ($showForm)
            <x-admin.create-record-button wire:click="createTemplate" />
        @endunless
    </div>

    @if ($showForm)
        <x-admin.side-panel-form section="campaign-template-form"
            card-id="admin-campaign-template-form-card" cancel-action="cancelForm">
            <form wire:submit="saveTemplate" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <x-admin.form-input name="name" id="name" :label="__('campaigns.admin.template_name')"
                        model="name" />

                    <x-admin.bilingual-rich-text-tabs :title="__('campaigns.admin.subject')" :locale-configs="$this->localeConfigsFor('subject', 'campaigns.admin.subject')"
                        mode="plain" :required-primary="false" />

                    <x-admin.bilingual-rich-text-tabs :title="__('campaigns.admin.body')" :locale-configs="$this->localeConfigsFor('body', 'campaigns.admin.body')"
                        :required-primary="false" />

                    <x-admin.form-single-radio-pills :legend="__('campaigns.admin.channel')" :options="$channelOptions"
                        model="channel" />
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="(bool) $editingId"
                    cancel-action="cancelForm" />
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200"
        data-campaign-template-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>{{ __('campaigns.admin.template_name') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.channel') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.created_at') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($templates as $template)
                <tr wire:key="campaign-template-{{ $template->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $template->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ __('campaigns.admin.channels.' . $template->channel) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $template->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions>
                            <x-admin.icon-button-edit
                                wire:click="editTemplate({{ $template->id }})" />
                            <x-admin.icon-button-delete
                                wire:click="confirmDelete({{ $template->id }})" />
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('campaigns.admin.empty_templates') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($templates->hasPages())
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @endif

    @if ($showDeleteModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="campaign-template-delete-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600" />
                    </div>
                    <div>
                        <h3 id="campaign-template-delete-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('campaigns.admin.delete_template_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('campaigns.admin.confirm_delete_template') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteTemplate"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
