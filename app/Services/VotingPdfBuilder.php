<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Voting;
use App\Models\VotingOption;
use App\Models\VotingOptionTotal;

class VotingPdfBuilder
{
    /**
     * @param  array<int, int>  $selectedVotingIds
     * @return array<string, mixed>
     */
    public function build(string $type, array $selectedVotingIds = []): array
    {
        $settings = Setting::stringValues([
            'front_site_name',
            'votings_pdf_delegated_text_eu',
            'votings_pdf_delegated_text_es',
            'votings_pdf_in_person_text_eu',
            'votings_pdf_in_person_text_es',
        ]);

        $siteName = trim((string) ($settings['front_site_name'] ?? ''));

        if ($siteName === '') {
            $siteName = (string) config('app.name', 'Madaia 33');
        }

        $textPrefix = $type === 'in_person' ? 'votings_pdf_in_person_text' : 'votings_pdf_delegated_text';

        $votingQuery = Voting::query()->with('options');

        if ($selectedVotingIds !== []) {
            // For admin-selected exports, include exactly selected rows, even if closed/unpublished.
            $votingQuery->whereIn('id', $selectedVotingIds);
        } else {
            // For public exports without explicit selection, keep active published behavior.
            $votingQuery->publishedOpen();
        }

        $votings = $votingQuery
            ->orderBy('starts_at')
            ->get()
            ->map(static function (Voting $voting): array {
                return [
                    'name_eu' => $voting->name_eu,
                    'name_es' => (string) ($voting->name_es ?? ''),
                    'question_eu' => $voting->question_eu,
                    'question_es' => (string) ($voting->question_es ?? ''),
                    'options' => $voting->options
                        ->map(static function ($option): array {
                            return [
                                'label_eu' => $option->label_eu,
                                'label_es' => (string) ($option->label_es ?? ''),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'documentType' => $type,
            'siteName' => $siteName,
            'leftHeader' => $siteName . ' Jabeen Erkidegoa',
            'rightHeader' => 'Comunidad de Propietarios/a ' . $siteName,
            'introEuHtml' => (string) ($settings[$textPrefix . '_eu'] ?? ''),
            'introEsHtml' => (string) ($settings[$textPrefix . '_es'] ?? ''),
            'faviconDataUri' => $this->faviconDataUri(),
            'votings' => $votings,
        ];
    }

    /**
     * @param  array<int, int>  $selectedVotingIds
     * @return array<string, mixed>
     */
    public function buildResults(array $selectedVotingIds = []): array
    {
        $settings = Setting::stringValues([
            'front_site_name',
        ]);

        $siteName = trim((string) ($settings['front_site_name'] ?? ''));

        if ($siteName === '') {
            $siteName = (string) config('app.name', 'Madaia 33');
        }

        $query = Voting::query()
            ->with(['options', 'optionTotals'])
            ->withCount('ballots')
            ->orderBy('starts_at');

        if ($selectedVotingIds !== []) {
            $query->whereIn('id', $selectedVotingIds);
        }

        $votings = $query
            ->get()
            ->map(function (Voting $voting): array {
                $totalsByOption = $voting->optionTotals->keyBy('voting_option_id');

                $options = $voting->options
                    ->map(function (VotingOption $option) use ($totalsByOption): array {
                        /** @var VotingOptionTotal|null $total */
                        $total = $totalsByOption->get($option->id);

                        return [
                            'label_eu' => $option->label_eu,
                            'label_es' => (string) ($option->label_es ?? ''),
                            'votes_count' => (int) ($total?->votes_count ?? 0),
                            'pct_total' => (float) ($total?->pct_total ?? 0),
                        ];
                    })
                    ->values();

                $maxVotes = (int) max(1, (int) $options->max('votes_count'));
                $maxPct = (float) max(1, (float) $options->max('pct_total'));
                $topVotes = (int) $options->max('votes_count');

                $optionsWithCharts = $options
                    ->map(static function (array $option) use ($maxVotes, $maxPct): array {
                        $voteChartPercent = $maxVotes > 0
                            ? round(($option['votes_count'] / $maxVotes) * 100, 2)
                            : 0.0;

                        $pctChartPercent = $maxPct > 0
                            ? round(($option['pct_total'] / $maxPct) * 100, 2)
                            : 0.0;

                        return [
                            ...$option,
                            'vote_chart_percent' => $voteChartPercent,
                            'pct_chart_percent' => $pctChartPercent,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'name_eu' => $voting->name_eu,
                    'name_es' => (string) ($voting->name_es ?? ''),
                    'question_eu' => $voting->question_eu,
                    'question_es' => (string) ($voting->question_es ?? ''),
                    'starts_at' => $voting->starts_at?->format('Y-m-d') ?? '-',
                    'ends_at' => $voting->ends_at?->format('Y-m-d') ?? '-',
                    'voters_count' => (int) $voting->ballots_count,
                    'total_votes_count' => (int) $options->sum('votes_count'),
                    'total_pct_sum' => (float) $options->sum('pct_total'),
                    'has_tie' => $topVotes > 0 && $options->where('votes_count', $topVotes)->count() > 1,
                    'top_votes' => $topVotes,
                    'options' => $optionsWithCharts,
                ];
            })
            ->values()
            ->all();

        return [
            'siteName' => $siteName,
            'leftHeader' => $siteName . ' Jabeen Erkidegoa',
            'rightHeader' => 'Comunidad de Propietarios/a ' . $siteName,
            'faviconDataUri' => $this->faviconDataUri(),
            'votings' => $votings,
        ];
    }

    private function faviconDataUri(): string
    {
        $svgPath = public_path('favicon.svg');

        if (is_file($svgPath)) {
            $content = file_get_contents($svgPath);

            if (is_string($content) && $content !== '') {
                return 'data:image/svg+xml;base64,' . base64_encode($content);
            }
        }

        $icoPath = public_path('favicon.ico');

        if (is_file($icoPath)) {
            $content = file_get_contents($icoPath);

            if (is_string($content) && $content !== '') {
                return 'data:image/x-icon;base64,' . base64_encode($content);
            }
        }

        return '';
    }
}
