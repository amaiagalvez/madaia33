<?php

namespace App\Services;

use App\Models\Voting;
use App\Models\Setting;
use App\Models\VotingOption;
use Illuminate\Support\Carbon;
use App\Models\VotingOptionTotal;
use Illuminate\Database\Eloquent\Collection;

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

        $siteName = $this->resolveSiteName($settings);

        $textPrefix = $type === 'in_person' ? 'votings_pdf_in_person_text' : 'votings_pdf_delegated_text';

        return [
            'documentType' => $type,
            'siteName' => $siteName,
            'leftHeader' => $siteName . ' Jabeen Erkidegoa',
            'rightHeader' => 'Comunidad de Propietarios/a ' . $siteName,
            'introEuHtml' => (string) ($settings[$textPrefix . '_eu'] ?? ''),
            'introEsHtml' => (string) ($settings[$textPrefix . '_es'] ?? ''),
            'faviconDataUri' => $this->faviconDataUri(),
            'votings' => $this->buildVotingsForDocument($selectedVotingIds),
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

        $siteName = $this->resolveSiteName($settings);
        $votings = $this->buildVotingsForResults($selectedVotingIds);

        return [
            'siteName' => $siteName,
            'leftHeader' => $siteName . ' Jabeen Erkidegoa',
            'rightHeader' => 'Comunidad de Propietarios/a ' . $siteName,
            'faviconDataUri' => $this->faviconDataUri(),
            'votings' => $votings,
        ];
    }

    /**
     * @param  array<string, string>  $settings
     */
    private function resolveSiteName(array $settings): string
    {
        $siteName = trim((string) ($settings['front_site_name'] ?? ''));

        if ($siteName === '') {
            return (string) config('app.name', '-');
        }

        return $siteName;
    }

    /**
     * @param  array<int, int>  $selectedVotingIds
     * @return array<int, array<string, mixed>>
     */
    private function buildVotingsForDocument(array $selectedVotingIds): array
    {
        $votingQuery = Voting::query()->with('options');

        if ($selectedVotingIds !== []) {
            // For admin-selected exports, include exactly selected rows, even if closed/unpublished.
            $votingQuery->whereIn('id', $selectedVotingIds);
        } else {
            // For public exports without explicit selection, keep active published behavior.
            $votingQuery->publishedOpen();
        }

        return $votingQuery
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Voting $voting): array => $this->mapVotingForDocument($voting))
            ->values()
            ->all();
    }

    /**
     * @return array{name_eu: string, name_es: string, question_eu: string, question_es: string, options: array<int, array{label_eu: string, label_es: string}>}
     */
    private function mapVotingForDocument(Voting $voting): array
    {
        return [
            'name_eu' => $voting->name_eu,
            'name_es' => (string) ($voting->name_es ?? ''),
            'question_eu' => $voting->question_eu,
            'question_es' => (string) ($voting->question_es ?? ''),
            'options' => $voting->options
                ->map(static fn (VotingOption $option): array => [
                    'label_eu' => $option->label_eu,
                    'label_es' => (string) ($option->label_es ?? ''),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<int, int>  $selectedVotingIds
     * @return array<int, array<string, mixed>>
     */
    private function buildVotingsForResults(array $selectedVotingIds): array
    {
        $query = Voting::query()
            ->with(['options', 'optionTotals'])
            ->withCount('ballots')
            ->orderBy('starts_at');

        if ($selectedVotingIds !== []) {
            $query->whereIn('id', $selectedVotingIds);
        }

        /** @var Collection<int, Voting> $votingModels */
        $votingModels = $query->get();

        return $votingModels
            ->map(fn (Voting $voting): array => $this->mapVotingForResults($voting))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label_eu: string, label_es: string, votes_count: int, pct_total: float}>
     */
    private function mapResultOptions(Voting $voting): array
    {
        $totalsByOption = $voting->optionTotals->keyBy('voting_option_id');

        return $voting->options
            ->map(function (VotingOption $option) use ($totalsByOption): array {
                /** @var VotingOptionTotal|null $total */
                $total = $totalsByOption->get($option->id);

                $votesCount = 0;
                $pctTotal = 0.0;

                if ($total instanceof VotingOptionTotal) {
                    $votesCount = (int) $total->votes_count;
                    $pctTotal = (float) $total->pct_total;
                }

                return [
                    'label_eu' => $option->label_eu,
                    'label_es' => (string) ($option->label_es ?? ''),
                    'votes_count' => $votesCount,
                    'pct_total' => $pctTotal,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{label_eu: string, label_es: string, votes_count: int, pct_total: float}>  $options
     * @return array<int, array{label_eu: string, label_es: string, votes_count: int, pct_total: float, vote_chart_percent: float, pct_chart_percent: float}>
     */
    private function withOptionCharts(array $options): array
    {
        $optionsCollection = collect($options);
        $maxVotes = (int) max(1, (int) $optionsCollection->max('votes_count'));
        $maxPct = (float) max(1, (float) $optionsCollection->max('pct_total'));

        return $optionsCollection
            ->map(static fn (array $option): array => [
                ...$option,
                'vote_chart_percent' => round(($option['votes_count'] / $maxVotes) * 100, 2),
                'pct_chart_percent' => round(($option['pct_total'] / $maxPct) * 100, 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVotingForResults(Voting $voting): array
    {
        $options = $this->mapResultOptions($voting);
        $optionsCollection = collect($options);
        $topVotes = (int) $optionsCollection->max('votes_count');

        return [
            'name_eu' => $voting->name_eu,
            'name_es' => (string) ($voting->name_es ?? ''),
            'question_eu' => $voting->question_eu,
            'question_es' => (string) ($voting->question_es ?? ''),
            'starts_at' => blank($voting->starts_at)
                ? '-'
                : Carbon::parse((string) $voting->starts_at)->format('Y-m-d'),
            'ends_at' => blank($voting->ends_at)
                ? '-'
                : Carbon::parse((string) $voting->ends_at)->format('Y-m-d'),
            'voters_count' => (int) $voting->ballots_count,
            'total_votes_count' => (int) $optionsCollection->sum('votes_count'),
            'total_pct_sum' => (float) $optionsCollection->sum('pct_total'),
            'has_tie' => $topVotes > 0 && $optionsCollection->where('votes_count', $topVotes)->count() > 1,
            'top_votes' => $topVotes,
            'options' => $this->withOptionCharts($options),
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
