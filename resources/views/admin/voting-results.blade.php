<x-layouts::admin.main :title="__('votings.admin.results_page_title')">
    <div class="max-w-7xl mx-auto">
        <x-admin.page-header :title="__('votings.admin.results_page_title')" />

        <div class="mb-5">
            <nav aria-label="{{ __('votings.admin.page_title') }}"
                class="inline-flex w-full items-center gap-2 rounded-xl border border-[#edd2c7]/70 bg-white/90 px-4 py-2.5 text-sm text-stone-600 shadow-xs"
                data-voting-results-breadcrumb>
                <a href="{{ route('admin.votings') }}"
                    class="inline-flex items-center gap-2 font-medium text-[#793d3d] transition-colors hover:text-brand-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    {{ __('votings.admin.page_title') }}
                </a>
                <span class="text-stone-300">/</span>
                <span class="font-medium text-stone-900">{{ $voting->name }}</span>
            </nav>
        </div>

        <div class="mb-5 space-y-1">
            <h2 class="text-xl font-semibold text-stone-900" data-voting-results-title>
                {{ $voting->name }}</h2>
            <p class="text-sm text-stone-700" data-voting-results-question>{{ $voting->question }}
            </p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white"
            data-voting-results-table>
            @php
                $totalVotedOwners = count($rows);
                $totalOwnerPercentage = (float) collect($rows)->sum('owner_percentage');
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
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('votings.admin.owner') }}
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('votings.admin.properties') }}
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('votings.admin.percentage') }}
                        </th>
                        @foreach ($options as $option)
                            <th
                                class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ $option['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($rows as $rowIndex => $row)
                        <tr>
                            <td class="px-4 py-2 text-gray-800">{{ $row['owner_name'] }}</td>
                            <td class="px-4 py-2 text-gray-600">
                                {{ $row['properties'] !== '' ? $row['properties'] : '—' }}</td>
                            <td class="px-4 py-2 text-gray-600">
                                {{ number_format((float) $row['owner_percentage'], 2, ',', '.') }}%
                            </td>
                            @foreach ($options as $option)
                                <td class="px-4 py-2 text-gray-600"
                                    data-owner-option-cell="{{ $rowIndex }}-{{ $option['id'] }}"
                                    data-option-value="{{ !$isAnonymous && (int) ($row['selected_option_id'] ?? 0) === (int) $option['id'] ? 'selected' : ($isAnonymous ? 'hidden' : 'empty') }}">
                                    @if (!$isAnonymous && (int) ($row['selected_option_id'] ?? 0) === (int) $option['id'])
                                        {{ number_format((float) $row['owner_percentage'], 2, ',', '.') }}%
                                    @else
                                        —
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($options) }}"
                                class="px-4 py-6 text-center text-gray-500">
                                {{ __('votings.admin.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                            data-total-voted-owners>
                            {{ __('votings.admin.results_total') }}: {{ $totalVotedOwners }}
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">
                            —
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700"
                            data-total-owner-percentage>
                            {{ number_format($totalOwnerPercentage, 2, ',', '.') }}%
                        </th>
                        @foreach ($options as $option)
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">
                                @if ($isAnonymous)
                                    <span data-total-option-votes-count="{{ $option['id'] }}">
                                        {{ $option['votes_count'] }}
                                    </span>
                                    <br>
                                @endif
                                <span data-total-option-percentage="{{ $option['id'] }}">
                                    {{ number_format((float) $option['total_percentage'], 2, ',', '.') }}%
                                </span>
                            </th>
                        @endforeach
                    </tr>
                    @if (!$isAnonymous)
                        <tr data-option-total-details-row>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600"
                                colspan="3">

                            </th>
                            @foreach ($options as $option)
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700"
                                    data-option-total-values="{{ $option['id'] }}"
                                    data-votes-count="{{ $option['votes_count'] }}"
                                    data-pct-total="{{ $option['pct_total'] }}"
                                    data-votes-count-mismatch="{{ $option['has_votes_count_mismatch'] ? '1' : '0' }}"
                                    data-pct-total-mismatch="{{ $option['has_pct_total_mismatch'] ? '1' : '0' }}">
                                    {{ __('votings.admin.results_votes_count_label') }}:
                                    <span data-votes-count-value="{{ $option['id'] }}"
                                        class="{{ $option['has_votes_count_mismatch'] ? 'font-semibold text-red-600' : 'text-gray-700' }}">
                                        {{ $option['votes_count'] }}
                                    </span>
                                    <br>
                                    {{ __('votings.admin.results_pct_total_label') }}:
                                    <span data-pct-total-value="{{ $option['id'] }}"
                                        class="{{ $option['has_pct_total_mismatch'] ? 'font-semibold text-red-600' : 'text-gray-700' }}">
                                        {{ number_format((float) $option['pct_total'], 2, ',', '.') }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <div class="mt-8" data-results-charts>
            <h3 class="mb-4 text-lg font-semibold text-stone-900">
                {{ __('votings.admin.results_charts_title') }}
            </h3>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-lg border border-gray-200 bg-white p-4"
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

                <section class="rounded-lg border border-gray-200 bg-white p-4"
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

                <section class="rounded-lg border border-gray-200 bg-white p-4"
                    data-chart-options-owners>
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

                <section class="rounded-lg border border-gray-200 bg-white p-4"
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
</x-layouts::admin.main>
