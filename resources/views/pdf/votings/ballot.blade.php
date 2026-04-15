<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <title>
        {{ $documentType === 'in_person' ? __('votings.front.download_in_person_pdf') : __('votings.front.download_delegated_pdf') }}
    </title>
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

        .intro-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .intro-column {
            width: 50%;
            vertical-align: top;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
        }

        .intro-column h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
        }

        .voting-block {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .voting-title {
            margin: 0 0 4px 0;
            font-size: 13px;
        }

        .question-eu {
            margin: 0;
            font-weight: 700;
        }

        .question-es {
            margin: 2px 0 8px 0;
            font-weight: 400;
        }

        .option-row {
            margin-bottom: 4px;
            font-size: 11px;
        }

        .checkbox {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #111827;
            margin-right: 6px;
            vertical-align: middle;
        }

        .option-eu {
            font-weight: 700;
        }

        .option-es {
            font-weight: 400;
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

    <table class="intro-columns">
        <tr>
            <td class="intro-column">
                {!! $introEuHtml !!}
            </td>
            <td class="intro-column">
                {!! $introEsHtml !!}
            </td>
        </tr>
    </table>

    @forelse ($votings as $voting)
        <section class="voting-block">
            <h3 class="question-eu">{{ $voting['question_eu'] }}</h3>
            @if ($voting['question_es'] !== '')
                <h4 class="question-es">{{ $voting['question_es'] }}</h4>
            @endif

            @foreach ($voting['options'] as $option)
                <div class="option-row">
                    <span class="checkbox"></span>
                    <span class="option-eu">{{ $option['label_eu'] }}</span>
                    @if ($option['label_es'] !== '')
                        <span class="option-es"> / {{ $option['label_es'] }}</span>
                    @endif
                </div>
            @endforeach
        </section>
    @empty
        <div class="empty-state">{{ __('votings.admin.empty') }}</div>
    @endforelse
</body>

</html>
