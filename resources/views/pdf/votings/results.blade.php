<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <title>{{ __('votings.pdf.results_title') }}</title>
    <style>
        @page {
            margin: 120px 38px 36px 38px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.4;
        }

        .page-header {
            position: fixed;
            top: -88px;
            left: 0;
            right: 0;
            width: 100%;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left,
        .header-center,
        .header-right {
            vertical-align: middle;
            font-size: 11px;
            color: #111827;
        }

        .header-left {
            width: 40%;
            text-align: left;
            font-weight: 700;
        }

        .header-center {
            width: 20%;
            text-align: center;
        }

        .header-right {
            width: 40%;
            text-align: right;
            font-weight: 400;
        }

        .header-favicon {
            height: 28px;
            width: 28px;
            object-fit: contain;
        }

        .voting-block {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .question-eu {
            margin: 0;
            font-weight: 700;
            font-size: 13px;
        }

        .question-es {
            margin: 2px 0 8px 0;
            font-weight: 400;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px 0;
        }

        .meta td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 11px;
        }

        .meta-label {
            color: #4b5563;
            display: block;
            font-size: 10px;
        }

        .meta-value {
            color: #111827;
            font-weight: 700;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }

        .results-table th {
            background: #f9fafb;
            font-weight: 700;
        }

        .option-eu {
            font-weight: 700;
        }

        .option-es {
            color: #4b5563;
            font-size: 10px;
        }

        .bar-wrap {
            width: 100%;
            background: #f3f4f6;
            border-radius: 999px;
            height: 8px;
            margin-top: 4px;
            overflow: hidden;
        }

        .bar-votes {
            height: 8px;
            background: #d9755b;
        }

        .bar-pct {
            height: 8px;
            background: #793d3d;
        }

        .winner {
            margin-top: 8px;
            font-size: 10px;
            color: #374151;
        }

        .empty-state {
            border: 1px dashed #9ca3af;
            padding: 12px;
            text-align: center;
            color: #4b5563;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <table class="header-table">
            <tr>
                <td class="header-left">{{ $leftHeader }}</td>
                <td class="header-center">
                    @if ($faviconDataUri !== '')
                        <img src="{{ $faviconDataUri }}" alt="favicon" class="header-favicon">
                    @endif
                </td>
                <td class="header-right">{{ $rightHeader }}</td>
            </tr>
        </table>
    </div>

    @forelse ($votings as $voting)
        <section class="voting-block">
            <p class="question-eu">{!! $voting['question_eu'] !!}</p>
            @if ($voting['question_es'] !== '')
                <p class="question-es">{!! $voting['question_es'] !!}</p>
            @endif

            <table class="meta">
                <tr>
                    <td>
                        <span class="meta-label">{{ __('votings.pdf.voted_people') }}</span>
                        <span class="meta-value">{{ $voting['voters_count'] }}</span>
                    </td>
                    <td>
                        <span class="meta-label">{{ __('votings.pdf.total_votes_count') }}</span>
                        <span class="meta-value">{{ $voting['total_votes_count'] }}</span>
                    </td>
                    <td>
                        <span class="meta-label">{{ __('votings.pdf.total_pct_sum') }}</span>
                        <span
                            class="meta-value">{{ number_format((float) $voting['total_pct_sum'], 2, ',', '.') }}%</span>
                    </td>
                    <td>
                        <span class="meta-label">{{ __('votings.pdf.period') }}</span>
                        <span class="meta-value">{{ $voting['starts_at'] }} -
                            {{ $voting['ends_at'] }}</span>
                    </td>
                </tr>
            </table>

            <table class="results-table">
                <thead>
                    <tr>
                        <th>{{ __('votings.pdf.option') }}</th>
                        <th>{{ __('votings.pdf.votes_count') }}</th>
                        <th>{{ __('votings.pdf.pct_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($voting['options'] as $option)
                        <tr>
                            <td>
                                <div class="option-eu">{{ $option['label_eu'] }}</div>
                                @if ($option['label_es'] !== '')
                                    <div class="option-es">{{ $option['label_es'] }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $option['votes_count'] }}</div>
                                <div class="bar-wrap">
                                    <div class="bar-votes"
                                        style="width: {{ $option['vote_chart_percent'] }}%;"></div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    {{ number_format((float) $option['pct_total'], 2, ',', '.') }}%
                                </div>
                                <div class="bar-wrap">
                                    <div class="bar-pct"
                                        style="width: {{ $option['pct_chart_percent'] }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="winner">
                @if ($voting['top_votes'] === 0)
                    {{ __('votings.pdf.no_votes_yet') }}
                @elseif ($voting['has_tie'])
                    {{ __('votings.pdf.tie_message') }}
                @else
                    {{ __('votings.pdf.leading_votes', ['count' => $voting['top_votes']]) }}
                @endif
            </p>
        </section>
    @empty
        <div class="empty-state">{{ __('votings.admin.empty') }}</div>
    @endforelse
</body>

</html>
