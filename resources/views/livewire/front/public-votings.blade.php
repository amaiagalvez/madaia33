<div>
    @if (session()->has('message'))
        <div
            class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if ($isDelegated)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
            data-votings-delegated-banner>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p>
                    {{ __('votings.front.delegated_mode', ['owner' => $activeOwner->coprop1_name]) }}
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
                <p class="mt-2 text-sm text-gray-600">{{ $voting->question }}</p>

                @if (in_array($voting->id, $votedVotingIds, true))
                    <div
                        class="mt-4 inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        {{ __('votings.front.already_voted') }}
                    </div>
                @else
                    <div class="mt-4 space-y-2">
                        @foreach ($voting->options as $option)
                            <label
                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                <input type="radio"
                                    wire:model="selectedOptions.{{ $voting->id }}"
                                    value="{{ $option->id }}"
                                    class="h-4 w-4 border-gray-300 text-[#d9755b] focus:ring-[#d9755b]">
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
