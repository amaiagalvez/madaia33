<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-end gap-2">
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
        <div class="fixed inset-0 z-40" data-section="voting-create-form">
            <button type="button" wire:click="$set('showCreateForm', false)"
                class="admin-slideover-backdrop absolute inset-0 bg-black/30"
                aria-label="{{ __('general.buttons.cancel') }}"></button>

            <div
                class="admin-slideover-panel absolute inset-y-0 right-0 z-50 h-full w-full max-w-4xl overflow-y-auto bg-white p-6 shadow-2xl">
                <form wire:submit="saveVoting" novalidate>
                    <div class="grid gap-4">
                        <x-admin.bilingual-rich-text-tabs :title="__('votings.admin.name')" :locale-configs="$this->localeConfigsFor('name', 'votings.admin.name')"
                            mode="plain" :required-primary="true" />

                        <x-admin.bilingual-rich-text-tabs :title="__('votings.admin.question')" :locale-configs="$this->localeConfigsFor('question', 'votings.admin.question')"
                            mode="plain" type="textarea" :rows="4" :required-primary="true" />

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="startsAt"
                                    class="block text-sm font-medium text-gray-700">{{ __('votings.admin.starts_at') }}</label>
                                <input id="startsAt" type="date" wire:model="startsAt"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                @error('startsAt')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="endsAt"
                                    class="block text-sm font-medium text-gray-700">{{ __('votings.admin.ends_at') }}</label>
                                <input id="endsAt" type="date" wire:model="endsAt"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                @error('endsAt')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="isPublished"
                                    class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]">
                                {{ __('votings.admin.is_published') }}
                            </label>

                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="isAnonymous"
                                    class="h-4 w-4 rounded border-gray-300 text-[#d9755b] focus:ring-[#d9755b]">
                                {{ __('votings.admin.is_anonymous') }}
                            </label>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-700">
                                {{ __('votings.admin.locations') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($locations as $location)
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" wire:model="selectedLocations"
                                            value="{{ $location->id }}" class="sr-only peer" />
                                        <span
                                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors peer-checked:bg-[#d9755b] peer-checked:text-white peer-checked:border-[#d9755b] border-gray-300 text-gray-600 hover:border-[#d9755b]/50 hover:bg-[#edd2c7]/20">
                                            {{ __('admin.locations.types.' . $location->type) }}
                                            {{ $location->code }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ __('votings.admin.options') }}</p>
                                <button type="button" wire:click="addOption"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    {{ __('votings.admin.add_option') }}
                                </button>
                            </div>

                            <div class="space-y-3">
                                @foreach ($options as $index => $option)
                                    <div wire:key="option-row-{{ $index }}"
                                        class="grid gap-3 md:grid-cols-12 md:items-end">
                                        <div class="md:col-span-5">
                                            <label class="block text-xs font-medium text-gray-600"
                                                for="optionEu{{ $index }}">{{ __('votings.admin.option_eu') }}</label>
                                            <input id="optionEu{{ $index }}" type="text"
                                                wire:model="options.{{ $index }}.labelEu"
                                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                            @error('options.' . $index . '.labelEu')
                                                <p class="mt-1 text-xs text-red-600">
                                                    {{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-5">
                                            <label class="block text-xs font-medium text-gray-600"
                                                for="optionEs{{ $index }}">{{ __('votings.admin.option_es') }}</label>
                                            <input id="optionEs{{ $index }}" type="text"
                                                wire:model="options.{{ $index }}.labelEs"
                                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]">
                                        </div>

                                        <div class="md:col-span-2 md:text-right">
                                            <button type="button"
                                                wire:click="removeOption({{ $index }})"
                                                class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                                                title="{{ __('general.buttons.delete') }}">
                                                <flux:icon.trash class="size-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                            {{ __('general.buttons.save') }}
                        </button>
                        <button type="button" wire:click="$set('showCreateForm', false)"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                            {{ __('general.buttons.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-admin.panel-table>
        <thead class="bg-gray-50">
            <tr>
                <th
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('votings.admin.name') }}</th>
                <th
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('votings.admin.starts_at') }}</th>
                <th
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('votings.admin.ends_at') }}</th>
                <th
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('votings.admin.is_published') }}</th>
                <th
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('votings.admin.census') }}</th>
                <th
                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    {{ __('votings.admin.votes') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($votings as $voting)
                <tr wire:key="voting-row-{{ $voting->id }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $voting->name }}
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
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="flex items-center gap-2">
                            <span>{{ $censusCounts[$voting->id] ?? 0 }}</span>
                            <button type="button" wire:click="openCensus({{ $voting->id }})"
                                class="rounded-full border border-transparent p-1.5 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]"
                                title="{{ __('votings.admin.open_census') }}">
                                <flux:icon.bars-3 class="size-4" />
                            </button>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="flex items-center gap-2">
                            <span>{{ $voting->ballots_count }}</span>
                            <button type="button" wire:click="openVoters({{ $voting->id }})"
                                class="rounded-full border border-transparent p-1.5 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]"
                                title="{{ __('votings.admin.open_voters') }}">
                                <flux:icon.bars-3 class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
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

    @if ($showOwnersModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4">
            <div class="mx-4 w-full max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-gray-900">{{ $ownersModalTitle }}</h3>
                    <button type="button" wire:click="closeOwnersModal"
                        class="text-sm text-gray-500 hover:text-gray-700">{{ __('general.close') }}</button>
                </div>

                <div class="max-h-96 overflow-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.owner') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.percentage') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.delegated_by') }}</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('votings.admin.delegate_dni') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($ownersModalRows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-gray-800">{{ $row['name'] }}</td>
                                    <td class="px-4 py-2 text-gray-600">
                                        %{{ number_format($row['percentage'], 2, ',', '.') }}%</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $row['delegated_by'] }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">{{ $row['delegate_dni'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
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
                                    {{ __('votings.admin.action') }}</th>
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
                                    {{ __('votings.admin.action') }}</th>
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
