<x-layouts::front.main :title="__('votings.front.results_page_title')">
    @push('meta')
        <meta name="description" content="{{ __('votings.front.results_seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="voting-results">
        <h1 class="sr-only">{{ __('votings.front.results_page_title') }}</h1>

        <div class="mb-5 rounded-xl border border-[#edd2c7]/70 bg-white/90 p-3 shadow-xs"
            data-voting-results-selector>
            <div class="flex flex-wrap gap-2">
                @foreach ($votingsWithResults as $resultVoting)
                    @php
                        $isCurrentVoting = $resultVoting->id === $voting->id;
                    @endphp
                    <a href="{{ route(\App\SupportedLocales::routeName('votings.results'), ['voting' => $resultVoting->id]) }}"
                        class="inline-flex min-h-10 items-center rounded-full border px-4 py-2 text-xs font-semibold transition-colors {{ $isCurrentVoting ? 'border-brand-600 bg-brand-600 text-white' : 'border-[#edd2c7] bg-white text-[#793d3d] hover:border-brand-400 hover:text-brand-600' }}"
                        data-results-selector-link="{{ $resultVoting->id }}">
                        {{ $resultVoting->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mb-5 space-y-1">
            <h2 class="text-xl font-semibold text-stone-900" data-voting-results-title>
                {{ $voting->name }}
            </h2>
            <p class="text-sm text-stone-700" data-voting-results-question>
                {{ $voting->question }}
            </p>
        </div>

        @php
            $participation = $charts['participation'] ?? [];
            $optionsChart = $charts['options'] ?? [];
            $participationEligibleOwners = (int) ($participation['eligible_owners'] ?? 0);
            $participationVotedOwners = (int) ($participation['voted_owners'] ?? 0);
            $participationEligiblePct = (float) ($participation['eligible_pct_total'] ?? 0);
            $participationVotedPct = (float) ($participation['voted_pct_total'] ?? 0);
            $participationOwnersMax = max(
                $participationEligibleOwners,
                $participationVotedOwners,
                1,
            );
            $participationPctMax = max($participationEligiblePct, $participationVotedPct, 1);
            $optionOwnersSeries = $optionsChart['owners'] ?? [];
            $optionPctSeries = $optionsChart['percentages'] ?? [];
            $optionOwnersMax = max(collect($optionOwnersSeries)->max('value') ?? 0, 1);
            $optionPctMax = max(collect($optionPctSeries)->max('value') ?? 0, 1);
        @endphp

        <div data-results-charts>
            <h3 class="mb-4 text-lg font-semibold text-stone-900">
                {{ __('votings.front.results_charts_title') }}
            </h3>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="section-shell overflow-hidden p-5 sm:p-6"
                    data-chart-participation-owners>
                    <h4 class="text-sm font-semibold text-stone-900">
                        {{ __('votings.admin.chart_participation_owners_title') }}
                    </h4>
                    <div class="mt-4 space-y-3">
                        <div>
                            <div
                                class="mb-1 flex items-center justify-between text-xs text-gray-700">
                                <span>{{ __('votings.admin.chart_eligible_owners') }}</span>
                                <span
                                    data-participation-eligible-owners>{{ $participationEligibleOwners }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full" data-participation-eligible-owners-bar
                                    style="width: {{ ($participationEligibleOwners / $participationOwnersMax) * 100 }}%; background-color: var(--color-brand-500);">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div
                                class="mb-1 flex items-center justify-between text-xs text-gray-700">
                                <span>{{ __('votings.admin.chart_voted_owners') }}</span>
                                <span
                                    data-participation-voted-owners>{{ $participationVotedOwners }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full" data-participation-voted-owners-bar
                                    style="width: {{ ($participationVotedOwners / $participationOwnersMax) * 100 }}%; background-color: var(--color-brand-900);">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-shell overflow-hidden p-5 sm:p-6"
                    data-chart-participation-percentage>
                    <h4 class="text-sm font-semibold text-stone-900">
                        {{ __('votings.admin.chart_participation_percentage_title') }}
                    </h4>
                    <div class="mt-4 space-y-3">
                        <div>
                            <div
                                class="mb-1 flex items-center justify-between text-xs text-gray-700">
                                <span>{{ __('votings.admin.chart_eligible_pct_total') }}</span>
                                <span
                                    data-participation-eligible-pct>{{ number_format($participationEligiblePct, 2, ',', '.') }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full" data-participation-eligible-pct-bar
                                    style="width: {{ ($participationEligiblePct / $participationPctMax) * 100 }}%; background-color: var(--color-brand-500);">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div
                                class="mb-1 flex items-center justify-between text-xs text-gray-700">
                                <span>{{ __('votings.admin.chart_voted_pct_total') }}</span>
                                <span
                                    data-participation-voted-pct>{{ number_format($participationVotedPct, 2, ',', '.') }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full" data-participation-voted-pct-bar
                                    style="width: {{ ($participationVotedPct / $participationPctMax) * 100 }}%; background-color: var(--color-brand-900);">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-shell overflow-hidden p-5 sm:p-6" data-chart-options-owners>
                    <h4 class="text-sm font-semibold text-stone-900">
                        {{ __('votings.admin.chart_options_owners_title') }}
                    </h4>
                    <div class="mt-4 space-y-3">
                        @foreach ($optionOwnersSeries as $series)
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs text-gray-700">
                                    <span>{{ $series['label'] }}</span>
                                    <span
                                        data-option-owners-chart-value="{{ $series['id'] }}">{{ $series['value'] }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full"
                                        data-option-owners-bar="{{ $series['id'] }}"
                                        style="width: {{ ((float) $series['value'] / $optionOwnersMax) * 100 }}%; background-color: var(--color-brand-600);">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="section-shell overflow-hidden p-5 sm:p-6"
                    data-chart-options-percentage>
                    <h4 class="text-sm font-semibold text-stone-900">
                        {{ __('votings.admin.chart_options_percentage_title') }}
                    </h4>
                    <div class="mt-4 space-y-3">
                        @foreach ($optionPctSeries as $series)
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs text-gray-700">
                                    <span>{{ $series['label'] }}</span>
                                    <span
                                        data-option-pct-chart-value="{{ $series['id'] }}">{{ number_format((float) $series['value'], 2, ',', '.') }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full"
                                        data-option-pct-bar="{{ $series['id'] }}"
                                        style="width: {{ ((float) $series['value'] / $optionPctMax) * 100 }}%; background-color: var(--color-brand-900);">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-layouts::front.main>
