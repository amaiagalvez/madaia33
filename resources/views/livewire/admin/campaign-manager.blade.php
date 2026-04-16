<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('admin.campaigns.templates') }}"
            class="inline-flex items-center rounded-md border border-[#d9755b] bg-white px-4 py-2 text-sm font-medium text-[#793d3d] shadow-sm transition hover:bg-[#edd2c7]/40">
            {{ __('campaigns.admin.templates') }}
        </a>
        <a href="{{ route('admin.campaigns.invalid-contacts') }}"
            class="inline-flex items-center rounded-md border border-[#d9755b] bg-white px-4 py-2 text-sm font-medium text-[#793d3d] shadow-sm transition hover:bg-[#edd2c7]/40">
            {{ __('campaigns.admin.invalid_contacts') }}
        </a>

        @unless ($showForm)
            <x-admin.create-record-button wire:click="createCampaign" />
        @endunless
    </div>

    @if ($showForm)
        <x-admin.side-panel-form section="campaign-create-form" card-id="admin-campaign-form-card"
            cancel-action="cancelForm">
            <form wire:submit="saveCampaign" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <div data-admin-field="select-template">
                        <label for="selectedTemplateId"
                            class="block text-sm font-medium text-stone-700">
                            {{ __('campaigns.admin.template') }}
                        </label>
                        <select id="selectedTemplateId" wire:model.live="selectedTemplateId"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                            <option value="">{{ __('campaigns.admin.no_template') }}</option>
                            @foreach ($templateOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-admin.bilingual-rich-text-tabs :title="__('campaigns.admin.subject')" :locale-configs="$this->localeConfigsFor('subject', 'campaigns.admin.subject')"
                        mode="plain" :required-primary="false" />

                    <x-admin.bilingual-rich-text-tabs :title="__('campaigns.admin.body')" :locale-configs="$this->localeConfigsFor('body', 'campaigns.admin.body')"
                        :required-primary="false" />

                    <x-admin.form-single-radio-pills :legend="__('campaigns.admin.channel')" :options="$channelOptions"
                        model="channel" />

                    <div data-admin-field="select-filter">
                        <x-admin.form-multi-checkbox-pills :legend="__('campaigns.admin.recipient_filter')" :options="$recipientFilterOptions"
                            model="recipientFilters" value-key="value" label-key="label" />

                        @error('recipientFilters')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('recipientFilters.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-admin.form-input name="scheduledAt" id="scheduledAt" type="datetime-local"
                        :label="__('campaigns.admin.scheduled_at')" model="scheduledAt" />

                    <x-admin.form-file-input id="campaignAttachments" model="attachments" multiple
                        :label="__('campaigns.admin.attachments')" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png"
                        :hint="__('campaigns.admin.attachments_help')" />

                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($storedAttachments !== [] || $attachments !== [])
                        <div class="space-y-4 rounded-xl border border-stone-200 bg-stone-50 p-4"
                            data-campaign-attachments-list>
                            @if ($storedAttachments !== [])
                                <div data-campaign-stored-attachments>
                                    <p class="text-sm font-semibold text-stone-900">
                                        {{ __('campaigns.admin.uploaded_documents') }}</p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach ($storedAttachments as $attachment)
                                            <li
                                                class="flex items-center justify-between rounded-lg border border-stone-200 bg-white px-3 py-2">
                                                <span
                                                    class="text-sm text-stone-700">{{ $attachment['filename'] }}</span>
                                                <x-admin.icon-button-delete
                                                    wire:click="removeStoredAttachment({{ $attachment['id'] }})"
                                                    title="{{ __('campaigns.admin.actions.remove_attachment') }}" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if ($attachments !== [])
                                <div data-campaign-pending-attachments>
                                    <p class="text-sm font-semibold text-stone-900">
                                        {{ __('campaigns.admin.pending_attachments') }}</p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach ($attachments as $index => $attachment)
                                            <li
                                                class="flex items-center justify-between rounded-lg border border-dashed border-[#d9755b]/40 bg-white px-3 py-2">
                                                <span
                                                    class="text-sm text-stone-700">{{ $attachment->getClientOriginalName() }}</span>
                                                <x-admin.icon-button-delete
                                                    wire:click="removePendingAttachment({{ $index }})"
                                                    title="{{ __('campaigns.admin.actions.remove_attachment') }}" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="rounded-xl border border-[#edd2c7] bg-[#edd2c7]/30 p-4"
                        data-campaign-recipient-counter>
                        <p class="text-sm font-semibold text-[#793d3d]">
                            {{ __('campaigns.admin.valid_recipients') }}:
                            {{ $recipientCountTotal }}
                        </p>
                        <p class="mt-1 text-xs text-stone-600">
                            {{ __('campaigns.admin.coprop1') }}:
                            {{ $recipientCountBySlot['coprop1'] }}
                            | {{ __('campaigns.admin.coprop2') }}:
                            {{ $recipientCountBySlot['coprop2'] }}
                        </p>

                        @if ($recipientCountTotal === 0)
                            <p class="mt-2 text-xs font-medium text-amber-700">
                                {{ __('campaigns.admin.no_recipients_warning') }}
                            </p>
                        @endif
                    </div>
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="(bool) $editingId"
                    cancel-action="cancelForm">
                    <button type="button" wire:click="saveAsTemplate"
                        class="inline-flex items-center rounded-md border border-[#d9755b] bg-white px-4 py-2 text-sm font-medium text-[#793d3d] shadow-sm transition hover:bg-[#edd2c7]/40 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2"
                        data-campaign-save-template>
                        {{ __('campaigns.admin.actions.save_template') }}
                    </button>
                </x-admin.form-footer-actions>
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200" data-campaign-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell sortable wire:click="sortBy('created_at')">
                    {{ __('campaigns.admin.subject') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.channel') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.recipient_filter') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell sortable
                    wire:click="sortBy('status')">{{ __('campaigns.admin.status') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.recipients_count') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell sortable
                    wire:click="sortBy('scheduled_at')">{{ __('campaigns.admin.scheduled_at') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell sortable
                    wire:click="sortBy('sent_at')">{{ __('campaigns.admin.sent_at') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($campaigns as $campaign)
                <tr wire:key="campaign-row-{{ $campaign->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.campaigns.show', $campaign) }}"
                            class="text-[#793d3d] underline-offset-2 hover:underline">
                            {{ $campaign->subject_eu ?? ($campaign->subject_es ?? '—') }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ __('campaigns.admin.channels.' . $campaign->channel) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if ($campaign->locations->isNotEmpty())
                            {{ $campaign->locations->filter(fn($location) => $location->location !== null)->map(
                                    fn($location) => __('campaigns.admin.filters.' . $location->location->type) .
                                        ' ' .
                                        $location->location->code,
                                )->implode(', ') }}
                        @else
                            {{ __('campaigns.admin.filters.all') }}
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ __('campaigns.admin.statuses.' . $campaign->status) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $campaign->recipients_count }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $campaign->sent_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.table-row-actions :bars-href="route('admin.campaigns.show', $campaign)" :bars-title="__('campaigns.admin.events')">
                            @if (in_array($campaign->status, ['draft', 'scheduled'], true))
                                <x-admin.icon-button-edit
                                    wire:click="editCampaign({{ $campaign->id }})" />
                                <x-admin.icon-button-delete
                                    wire:click="confirmDelete({{ $campaign->id }})" />
                            @endif

                            <x-admin.action-link-confirm
                                wire:click="confirmAction({{ $campaign->id }}, 'duplicate')"
                                title="{{ __('campaigns.admin.actions.duplicate') }}"
                                state="neutral">
                                <flux:icon.document-duplicate class="size-4" />
                            </x-admin.action-link-confirm>

                            @if ($campaign->status === 'draft')
                                <x-admin.action-link-confirm
                                    wire:click="confirmAction({{ $campaign->id }}, 'send')"
                                    title="{{ __('campaigns.admin.actions.send') }}"
                                    state="success">
                                    <flux:icon.paper-airplane class="size-4" />
                                </x-admin.action-link-confirm>

                                <x-admin.action-link-confirm
                                    wire:click="confirmAction({{ $campaign->id }}, 'schedule')"
                                    title="{{ __('campaigns.admin.actions.schedule') }}"
                                    state="neutral">
                                    <flux:icon.clock class="size-4" />
                                </x-admin.action-link-confirm>
                            @endif

                            @if ($campaign->status === 'scheduled')
                                <x-admin.action-link-confirm
                                    wire:click="confirmAction({{ $campaign->id }}, 'cancel_schedule')"
                                    title="{{ __('campaigns.admin.actions.cancel_schedule') }}"
                                    state="danger">
                                    <flux:icon.x-circle class="size-4" />
                                </x-admin.action-link-confirm>
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('campaigns.admin.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($campaigns->hasPages())
        <div class="mt-6">
            {{ $campaigns->links() }}
        </div>
    @endif

    @if ($showActionModal)
        @php
            $actionTone = match ($confirmingAction) {
                'send' => 'success',
                'cancel_schedule' => 'danger',
                default => 'neutral',
            };

            $actionTitle = match ($confirmingAction) {
                'duplicate' => __('campaigns.admin.actions.duplicate'),
                'send' => __('campaigns.admin.actions.send'),
                'schedule' => __('campaigns.admin.actions.schedule'),
                'cancel_schedule' => __('campaigns.admin.actions.cancel_schedule'),
                default => __('general.buttons.confirm'),
            };

            $actionMessage = match ($confirmingAction) {
                'duplicate' => __('campaigns.admin.confirm_duplicate'),
                'send' => __('campaigns.admin.confirm_send'),
                'schedule' => __('campaigns.admin.confirm_schedule'),
                'cancel_schedule' => __('campaigns.admin.confirm_cancel_schedule'),
                default => '',
            };
        @endphp

        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="campaign-action-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $actionTone === 'success' ? 'bg-green-100' : ($actionTone === 'danger' ? 'bg-red-100' : 'bg-amber-100') }}">
                        @if ($confirmingAction === 'send')
                            <flux:icon.paper-airplane class="size-5 text-green-600" />
                        @elseif ($confirmingAction === 'cancel_schedule')
                            <flux:icon.x-circle class="size-5 text-red-600" />
                        @elseif ($confirmingAction === 'duplicate')
                            <flux:icon.document-duplicate class="size-5 text-amber-600" />
                        @else
                            <flux:icon.clock class="size-5 text-amber-600" />
                        @endif
                    </div>
                    <div>
                        <h3 id="campaign-action-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ $actionTitle }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $actionMessage }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelAction"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="doAction"
                        class="rounded-md px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 {{ $actionTone === 'success' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : ($actionTone === 'danger' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400') }}">
                        {{ __('general.buttons.confirm') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif

    @if ($showDeleteModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="campaign-delete-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600" />
                    </div>
                    <div>
                        <h3 id="campaign-delete-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('campaigns.admin.delete_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('campaigns.admin.confirm_delete') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteCampaign"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif
</div>
