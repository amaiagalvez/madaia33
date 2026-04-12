<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-end gap-2">
        <button type="button" wire:click="downloadDelegatedPdf"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2"
            data-action="download-delegated-vote-pdf">
            {{ __('votings.admin.download_delegated_pdf') }}
        </button>

        <button type="button" wire:click="downloadInPersonPdf"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2"
            data-action="download-in-person-vote-pdf">
            {{ __('votings.admin.download_in_person_pdf') }}
        </button>

        <x-admin.create-record-button wire:click="createVoting" />

        <button type="button" wire:click="openInPersonVoteModal"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2"
            data-action="open-in-person-vote">
            {{ __('votings.admin.in_person_vote') }}
        </button>

        <button type="button" wire:click="openDelegatedVoteModal"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2"
            data-action="open-delegated-vote">
            {{ __('votings.admin.delegated_vote') }}
        </button>
    </div>

    @if ($showCreateForm)
        <x-admin.side-panel-form section="voting-create-form" card-id="admin-voting-form-card"
            cancel-action="cancelVoting">
            <form wire:submit="saveVoting" novalidate>
                <div class="grid grid-cols-1 gap-4">
                    <x-admin.bilingual-rich-text-tabs :title="__('votings.admin.name')" :locale-configs="$this->localeConfigsFor('name', 'votings.admin.name')"
                        mode="plain" :required-primary="true" />

                    <x-admin.bilingual-rich-text-tabs :title="__('votings.admin.question')" :locale-configs="$this->localeConfigsFor('question', 'votings.admin.question')"
                        :required-primary="true" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.form-date-input :label="__('votings.admin.starts_at')" model="startsAt" />
                        <x-admin.form-date-input :label="__('votings.admin.ends_at')" model="endsAt" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.form-boolean-toggle :label="__('votings.admin.is_published')" model="isPublished"
                            :value="$isPublished" :true-label="__('admin.common.yes')" :false-label="__('admin.common.no')" />

                        <x-admin.form-boolean-toggle :label="__('votings.admin.is_anonymous')" model="isAnonymous"
                            :value="$isAnonymous" :true-label="__('admin.common.yes')" :false-label="__('admin.common.no')" />
                    </div>

                    <x-admin.form-multi-checkbox-pills :legend="__('votings.admin.locations')" :options="$locations"
                        model="selectedLocations" value-key="id" label-key="label" />

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-semibold text-stone-800">
                                {{ __('votings.admin.options') }}</p>
                            @if ($editingVotingBallotCount === 0)
                                <button type="button" wire:click="addOption"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    {{ __('votings.admin.add_option') }}
                                </button>
                            @endif
                        </div>

                        <div class="space-y-3">
                            @foreach ($options as $index => $option)
                                <div wire:key="option-row-{{ $index }}"
                                    class="grid gap-3 md:grid-cols-12 md:items-end">
                                    <div class="md:col-span-5">
                                        <label class="block text-xs font-medium text-stone-700"
                                            for="optionEu{{ $index }}">{{ __('votings.admin.option_eu') }}</label>
                                        <input id="optionEu{{ $index }}" type="text"
                                            wire:model="options.{{ $index }}.labelEu"
                                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                        @error('options.' . $index . '.labelEu')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-5">
                                        <label class="block text-xs font-medium text-stone-700"
                                            for="optionEs{{ $index }}">{{ __('votings.admin.option_es') }}</label>
                                        <input id="optionEs{{ $index }}" type="text"
                                            wire:model="options.{{ $index }}.labelEs"
                                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                    </div>

                                    <div class="md:col-span-2 md:text-right">
                                        @if ($editingVotingBallotCount === 0)
                                            <button type="button"
                                                wire:click="removeOption({{ $index }})"
                                                class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                                                title="{{ __('general.buttons.delete') }}">
                                                <flux:icon.trash class="size-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-admin.form-footer-actions show-default-buttons :is-editing="(bool) $editingVotingId"
                    cancel-action="cancelVoting" />
            </form>
        </x-admin.side-panel-form>
    @endif

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell class="w-12">
                    <span class="sr-only">{{ __('votings.admin.select_for_pdf') }}</span>
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.name') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.locations') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.starts_at') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.ends_at') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.is_published') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.is_anonymous') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.census') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell>
                    {{ __('votings.admin.voters') }}
                </x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($votings as $voting)
                <tr wire:key="voting-row-{{ $voting->id }}">
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <input type="checkbox" wire:model="selectedVotingIds"
                            value="{{ $voting->id }}"
                            aria-label="{{ __('votings.admin.select_for_pdf') }}"
                            class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]">
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $voting->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $voting->locations->map(fn($vl) => $vl->location?->code)->filter()->join(', ') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $voting->starts_at?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $voting->ends_at?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if ($voting->is_published)
                            <flux:icon.check-circle class="size-4 text-green-600" />
                        @else
                            <flux:icon.x-circle class="size-4 text-red-500" />
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if ($voting->is_anonymous)
                            <flux:icon.check-circle class="size-4 text-green-600" />
                        @else
                            <flux:icon.x-circle class="size-4 text-red-500" />
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="flex items-center gap-2">
                            <span>{{ $censusCounts[$voting->id] ?? 0 }}</span>
                            <button type="button" wire:click="openCensus({{ $voting->id }})"
                                class="rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]"
                                title="{{ __('votings.admin.open_census') }}">
                                <flux:icon.users class="size-4" />
                                <span class="sr-only">{{ __('votings.admin.open_census') }}</span>
                            </button>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="flex items-center gap-2">
                            <span>{{ $voting->ballots_count }}</span>
                            <button type="button" wire:click="openVoters({{ $voting->id }})"
                                class="rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]"
                                title="{{ __('votings.admin.open_voters') }}">
                                <flux:icon.list-bullet class="size-4" />
                                <span class="sr-only">{{ __('votings.admin.open_voters') }}</span>
                            </button>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        @if ($voting->ballots_count === 0)
                            <x-admin.table-row-actions>
                                <x-admin.icon-button-edit
                                    wire:click="editVoting({{ $voting->id }})" />
                                <x-admin.icon-button-delete
                                    wire:click="confirmDeleteVoting({{ $voting->id }})" />
                            </x-admin.table-row-actions>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('votings.admin.empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>

    @if ($votings->hasPages())
        <div class="mt-6">
            {{ $votings->links() }}
        </div>
    @endif

    @if ($showDeleteModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="voting-delete-modal-title">
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
                        <h3 id="voting-delete-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('votings.admin.delete_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('votings.admin.confirm_delete') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelDeleteVoting"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteVoting"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif

    @if ($showOwnersModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4">
            <div class="mx-4 w-full max-w-7xl space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-gray-900">{{ $ownersModalTitle }}</h3>
                    <button type="button" wire:click="closeOwnersModal"
                        class="text-sm text-gray-500 hover:text-gray-700">{{ __('general.close') }}</button>
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.owner') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.properties') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.percentage') }}</th>
                                @unless ($ownersModalIsAnonymous)
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        {{ __('votings.admin.vote') }}</th>
                                @endunless
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.delegated_vote') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.delegated_by') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($ownersModalRows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-gray-800">
                                        <div class="flex items-center gap-2">
                                            @if ($row['has_voted'] ?? false)
                                                <flux:icon.check-circle
                                                    class="size-4 shrink-0 text-green-600" />
                                            @endif
                                            {{ $row['name'] }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['properties'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ number_format($row['percentage'], 2, ',', '.') }}%</td>
                                    @unless ($ownersModalIsAnonymous)
                                        <td class="px-4 py-2 text-gray-600">{{ $row['vote'] ?: '—' }}
                                        </td>
                                    @endunless
                                    <td class="px-4 py-2 text-gray-600">{{ $row['delegate_dni'] }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">{{ $row['delegated_by'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $ownersModalIsAnonymous ? 5 : 6 }}"
                                        class="px-4 py-6 text-center text-gray-500">
                                        {{ __('votings.admin.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </dialog>
    @endif

    @if ($showDelegatedModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4">
            <div class="mx-4 w-full max-w-6xl space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ __('votings.admin.delegated_modal_title') }}</h3>
                    <button type="button" wire:click="closeDelegatedVoteModal"
                        class="text-sm text-gray-500 hover:text-gray-700">{{ __('general.close') }}</button>
                </div>

                <div>
                    <label for="delegatedSearch"
                        class="sr-only">{{ __('votings.admin.delegated_search') }}</label>
                    <input id="delegatedSearch" type="text"
                        wire:model.live.debounce.300ms="delegatedSearch"
                        placeholder="{{ __('votings.admin.delegated_search_placeholder') }}"
                        class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.owner') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.portal_codes') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.local_codes') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.garage_codes') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.pending_votings') }}</th>
                                <th
                                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($filteredDelegatedRows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-gray-800">
                                        <p>{{ $row['owner_name'] }}</p>
                                        @if ($row['owner_secondary_name'] !== '')
                                            <p class="text-xs text-gray-500">
                                                {{ $row['owner_secondary_name'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['portal_codes'] !== '' ? $row['portal_codes'] : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['local_codes'] !== '' ? $row['local_codes'] : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['garage_codes'] !== '' ? $row['garage_codes'] : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['pending_votings'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button"
                                            wire:click="startDelegatedVote({{ $row['owner_id'] }})"
                                            class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                            {{ __('votings.admin.vote_as_owner') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-4 py-6 text-center text-gray-500">
                                        {{ __('votings.admin.no_pending_delegations') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </dialog>
    @endif

    @if ($showInPersonModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4">
            <div class="mx-4 w-full max-w-6xl space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ __('votings.admin.in_person_modal_title') }}</h3>
                    <button type="button" wire:click="closeInPersonVoteModal"
                        class="text-sm text-gray-500 hover:text-gray-700">{{ __('general.close') }}</button>
                </div>

                <div>
                    <label for="inPersonSearch"
                        class="sr-only">{{ __('votings.admin.in_person_search') }}</label>
                    <input id="inPersonSearch" type="text"
                        wire:model.live.debounce.300ms="inPersonSearch"
                        placeholder="{{ __('votings.admin.in_person_search_placeholder') }}"
                        class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.owner') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.portal_codes') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.local_codes') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.garage_codes') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.pending_votings') }}</th>
                                <th
                                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($filteredInPersonRows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-gray-800">
                                        <p>{{ $row['owner_name'] }}</p>
                                        @if ($row['owner_secondary_name'] !== '')
                                            <p class="text-xs text-gray-500">
                                                {{ $row['owner_secondary_name'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['portal_codes'] !== '' ? $row['portal_codes'] : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['local_codes'] !== '' ? $row['local_codes'] : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['garage_codes'] !== '' ? $row['garage_codes'] : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $row['pending_votings'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button"
                                            wire:click="startInPersonVote({{ $row['owner_id'] }})"
                                            class="inline-flex items-center rounded-md bg-[#d9755b] px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                                            {{ __('votings.admin.vote_as_owner') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-4 py-6 text-center text-gray-500">
                                        {{ __('votings.admin.no_pending_delegations') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </dialog>
    @endif
</div>
