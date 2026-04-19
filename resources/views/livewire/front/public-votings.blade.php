<div x-data x-init="if (window.isSecureContext && navigator.geolocation) { navigator.geolocation.getCurrentPosition((position) => { $wire.setVoteCoordinates(position.coords.latitude, position.coords.longitude); }); }">
    @if ($requiresTermsAcceptance)
        <div class="fixed inset-0 z-80 bg-black/50" aria-hidden="true"></div>
        <section class="fixed inset-0 z-90 grid place-items-center p-4" data-votings-terms-modal>
            <div
                class="w-full max-w-3xl rounded-2xl border border-amber-300 bg-amber-50 p-6 shadow-2xl">
                <h2 class="text-base font-semibold text-amber-900">
                    {{ $termsScope === 'vote_delegate' ? __('votings.front.delegated_terms_title') : __('profile.terms.title') }}
                </h2>
                <div class="prose prose-sm mt-3 max-h-72 overflow-y-auto max-w-none text-amber-900">
                    {!! $termsHtml !!}
                </div>

                <form method="POST"
                    action="{{ route(\App\SupportedLocales::routeName('profile.terms.accept')) }}"
                    class="mt-5">
                    @csrf
                    <input type="hidden" name="terms_scope" value="{{ $termsScope }}">
                    <input type="hidden" name="return_to"
                        value="{{ route(\App\SupportedLocales::routeName('votings'), absolute: false) }}">
                    <button type="submit" data-votings-terms-accept-button
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#793d3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#5d2e2e]">
                        {{ __('profile.terms.accept_button') }}
                    </button>
                </form>
            </div>
        </section>
    @endif

    <div class="{{ $requiresTermsAcceptance ? 'pointer-events-none select-none blur-sm' : '' }}"
        data-votings-content>
        <div class="mb-6 flex flex-wrap items-center justify-end gap-2" data-votings-pdf-actions>
            <a href="{{ route(\App\SupportedLocales::routeName('votings.pdf.delegated')) }}"
                class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 text-stone-600 bg-[#edd2c7]/45 hover:text-[#793d3d]"
                data-front-download-delegated-pdf>
                {{ __('votings.front.download_delegated_pdf') }}
            </a>

            <a href="{{ route(\App\SupportedLocales::routeName('votings.pdf.in_person')) }}"
                class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 text-stone-600 bg-[#edd2c7]/45 hover:text-[#793d3d]"
                data-front-download-in-person-pdf>
                {{ __('votings.front.download_in_person_pdf') }}
            </a>
        </div>

        <article class="mb-6 rounded-xl border border-[#edd2c7] bg-white p-5 shadow-sm"
            data-votings-explanation-card>
            <h2 class="text-base font-semibold text-[#793d3d]">
                {{ __('votings.front.explanation_title') }}
            </h2>
            <div class="prose prose-sm mt-3 max-w-none text-gray-700">
                {!! $votingsExplanationHtml !!}
            </div>
        </article>

        @if ($canManageDelegatedVoting && !$isDelegated && !$isInPersonVoting)
            <div class="mb-6 flex items-center justify-end gap-2" data-votings-delegated-action>
                <button type="button" wire:click="openInPersonVoteModal"
                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 text-stone-600 bg-[#edd2c7]/45 hover:text-[#793d3d]"
                    data-open-front-in-person-modal>
                    {{ __('votings.front.in_person_vote_button') }}
                </button>

                <button type="button" wire:click="openDelegatedVoteModal"
                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 text-stone-600 bg-[#edd2c7]/45 hover:text-[#793d3d]"
                    data-open-front-delegated-modal>
                    {{ __('votings.front.delegated_vote_button') }}
                </button>
            </div>
        @endif

        @if (session()->has('message'))
            <div
                class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('message') }}
            </div>
        @endif

        @if (
            !$requiresTermsAcceptance &&
                !$canCastVotes &&
                $canManageDelegatedVoting &&
                !$isDelegated &&
                !$isInPersonVoting)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                data-votings-select-delegated-owner>
                {{ __('votings.front.select_delegated_owner') }}
            </div>
        @elseif (!$requiresTermsAcceptance && !$canCastVotes)
            <div
                class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                {{ __('votings.front.read_only_superadmin') }}
            </div>
        @endif

        @if ($isDelegated)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                data-votings-delegated-banner>
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p>
                            {{ __('votings.front.delegated_mode', ['owner' => $activeOwner->coprop1_name]) }}
                        </p>
                        <button type="button" wire:click="clearDelegatedMode"
                            class="inline-flex items-center rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                            {{ __('votings.front.leave_delegated_mode') }}
                        </button>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label for="delegateDniInput" class="text-xs font-semibold text-amber-700">
                            {{ __('votings.front.delegate_dni_label') }}
                        </label>
                        <input id="delegateDniInput" type="text" wire:model.live="delegateDni"
                            placeholder="{{ __('votings.front.delegate_dni_placeholder') }}"
                            class="block w-full max-w-xs rounded-md border border-amber-300 bg-white px-3 py-1.5 text-sm text-stone-900 shadow-sm focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600"
                            data-delegate-dni-input>
                        @error('delegateDni')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        @if ($isInPersonVoting)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                data-votings-in-person-banner>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        {{ __('votings.front.in_person_mode', ['owner' => $activeOwner->coprop1_name]) }}
                    </p>
                    <button type="button" wire:click="clearDelegatedMode"
                        class="inline-flex items-center rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                        {{ __('votings.front.leave_delegated_mode') }}
                    </button>
                </div>
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($votings as $voting)
                <article class="section-shell overflow-hidden p-5 sm:p-6"
                    wire:key="public-voting-card-{{ $voting->id }}"
                    data-voting-card="{{ $voting->id }}">
                    <h2 class="text-xl font-bold tracking-tight text-gray-900">
                        {{ $voting->name }}
                    </h2>
                    <div class="prose prose-sm mt-2 max-w-none text-gray-600"
                        data-voting-question="{{ $voting->id }}">
                        {!! $voting->question !!}
                    </div>

                    @if (in_array($voting->id, $votedVotingIds, true))
                        <div
                            class="mt-4 inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            {{ __('votings.front.already_voted') }}
                        </div>
                    @elseif (!$canCastVotes)
                        <div
                            class="mt-4 inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ __('votings.front.read_only_superadmin') }}
                        </div>
                    @else
                        <div class="mt-4 space-y-2">
                            @foreach ($voting->options as $option)
                                <label
                                    class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                    <input type="radio"
                                        wire:model="selectedOptions.{{ $voting->id }}"
                                        value="{{ $option->id }}"
                                        class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-600">
                                    <span>{{ $option->label }}</span>
                                </label>
                            @endforeach

                            @error('selectedOptions.' . $voting->id)
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="button" wire:click="vote({{ $voting->id }})"
                                class="btn-brand inline-flex min-h-11 items-center justify-center"
                                data-vote-submit="{{ $voting->id }}">
                                {{ __('votings.front.vote_button') }}
                            </button>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

    </div>

    <template x-teleport="body">
        <div x-show="$wire.showDelegatedModal">
            <div class="fixed inset-0 z-100 bg-black/50" aria-hidden="true"></div>
            <div class="fixed inset-0 z-110 grid place-items-center p-4">
                <div class="w-full max-w-6xl space-y-4 rounded-xl bg-white p-6 shadow-2xl"
                    data-front-delegated-modal>
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ __('votings.front.delegated_modal_title') }}</h3>
                        <button type="button" wire:click="closeDelegatedVoteModal"
                            class="text-sm text-gray-500 hover:text-gray-700">
                            {{ __('general.close') }}
                        </button>
                    </div>

                    <div>
                        <label for="delegatedSearch"
                            class="sr-only">{{ __('votings.front.delegated_search') }}</label>
                        <input id="delegatedSearch" type="text"
                            wire:model.live.debounce.300ms="delegatedSearch"
                            placeholder="{{ __('votings.front.delegated_search_placeholder') }}"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
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
                                                class="btn-brand inline-flex min-h-11 items-center justify-center"
                                                data-front-vote-as-owner="{{ $row['owner_id'] }}">
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
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="$wire.showInPersonModal">
            <div class="fixed inset-0 z-100 bg-black/50" aria-hidden="true"></div>
            <div class="fixed inset-0 z-110 grid place-items-center p-4">
                <div class="w-full max-w-6xl space-y-4 rounded-xl bg-white p-6 shadow-2xl"
                    data-front-in-person-modal>
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ __('votings.front.in_person_modal_title') }}</h3>
                        <button type="button" wire:click="closeInPersonVoteModal"
                            class="text-sm text-gray-500 hover:text-gray-700">
                            {{ __('general.close') }}
                        </button>
                    </div>

                    <div>
                        <label for="inPersonSearch"
                            class="sr-only">{{ __('votings.front.in_person_search') }}</label>
                        <input id="inPersonSearch" type="text"
                            wire:model.live.debounce.300ms="inPersonSearch"
                            placeholder="{{ __('votings.front.in_person_search_placeholder') }}"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
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
                                                class="btn-brand inline-flex min-h-11 items-center justify-center"
                                                data-front-in-person-vote-as-owner="{{ $row['owner_id'] }}">
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
            </div>
        </div>
    </template>
</div>
