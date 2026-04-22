<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <title>
        {{ $documentType === 'in_person' ? __('votings.front.download_in_person_pdf') : __('votings.front.download_delegated_pdf') }}
    </title>
    <style>
        @page {
            margin: 104px 38px 36px 38px;
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

        .intro-block {
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            margin-bottom: 14px;
        }

        .explanation-block {
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            margin-bottom: 14px;
        }

        .voting-block {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .question-eu {
            margin: 0 0 8px 0;
            font-size: 13px;
            font-weight: 700;
        }

        .question-es {
            margin: 0 0 8px 0;
            font-size: 13px;
            font-weight: 700;
        }

        .option-row {
            display: inline-flex;
            align-items: center;
            margin-right: 14px;
            margin-bottom: 2px;
            margin-left: 12px;
            font-size: 11px;
            white-space: nowrap;
        }

        .checkbox {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #111827;
            margin-right: 6px;
        }

        .option-label {
            font-weight: 400;
        }

        .empty-state {
            border: 1px dashed #9ca3af;
            padding: 12px;
            text-align: center;
            color: #4b5563;
        }

        .language-break {
            page-break-before: always;
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

    @if ($introEuHtml !== '')
        <div class="intro-block">{!! $introEuHtml !!}</div>
    @endif

    @if ($votingsExplanationEuHtml !== '')
        <div class="explanation-block">{!! $votingsExplanationEuHtml !!}</div>
    @endif

    @forelse ($votings as $voting)
        <section class="voting-block">
            <h3 class="question-eu">{!! $voting['question_eu'] !!}</h3>

            @foreach ($voting['options'] as $option)
                <div class="option-row">
                    <span class="checkbox"></span>
                    <span class="option-label">{!! $option['label_eu'] !!}</span>
                </div>
            @endforeach
        </section>
    @empty
        <div class="empty-state">{{ __('votings.admin.empty') }}</div>
    @endforelse

    <div class="language-break"></div>

    @if ($introEsHtml !== '')
        <div class="intro-block">{!! $introEsHtml !!}</div>
    @endif

    @if ($votingsExplanationEsHtml !== '')
        <div class="explanation-block">{!! $votingsExplanationEsHtml !!}</div>
    @endif

    @forelse ($votings as $voting)
        <section class="voting-block">
            <h3 class="question-es">{!! $voting['question_es'] !== '' ? $voting['question_es'] : $voting['question_eu'] !!}</h3>

            @foreach ($voting['options'] as $option)
                <div class="option-row">
                    <span class="checkbox"></span>
                    <span class="option-label">{!! $option['label_es'] !== '' ? $option['label_es'] : $option['label_eu'] !!}</span>
                </div>
            @endforeach
        </section>
    @empty
        <div class="empty-state">{{ __('votings.admin.empty') }}</div>
    @endforelse
</body>

</html>
