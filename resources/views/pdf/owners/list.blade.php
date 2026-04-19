<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <title>{{ __('admin.owners.pdf.title') }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.3;
        }

        h1 {
            margin: 0 0 8px 0;
            font-size: 14px;
        }

        .generated-at {
            margin: 0 0 12px 0;
            font-size: 9px;
            color: #4b5563;
        }

        .filters {
            margin: 0 0 12px 0;
            font-size: 9px;
            color: #374151;
        }

        .filters strong {
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
        }

        thead th {
            background: #f3f4f6;
            text-align: center;
            font-weight: 700;
        }

        .subheader {
            font-size: 9px;
            font-weight: 700;
            background: #f9fafb;
        }

        .owner-id {
            text-align: center;
            white-space: nowrap;
        }

        .line {
            margin-bottom: 3px;
            border-bottom: 1px dashed #e5e7eb;
            padding-bottom: 2px;
        }

        .line:last-child {
            margin-bottom: 0;
            border-bottom: 0;
            padding-bottom: 0;
        }

        .empty {
            color: #9ca3af;
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .contact-line {
            margin-top: 2px;
        }

        .invalid-contact {
            text-decoration: line-through;
            text-decoration-thickness: 1px;
        }

        .whatsapp-check {
            margin-left: 4px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <h1>{{ __('admin.owners.pdf.title') }}</h1>
    <p class="generated-at">{{ __('admin.owners.pdf.generated_at') }}:
        {{ now()->format('d/m/Y H:i') }}</p>
    @if (($appliedFilters ?? []) !== [])
        <p class="filters">
            <strong>{{ __('admin.owners.pdf.applied_filters') }}:</strong>
            {{ implode(' · ', $appliedFilters) }}
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th rowspan="2">{{ __('admin.owners.columns.num') }}</th>
                <th colspan="2">KoJabea1</th>
                <th colspan="2">KoJabea2</th>
                <th rowspan="2">Atariak</th>
                <th rowspan="2">Garajeak</th>
                <th rowspan="2">Trastelekuak</th>
                <th rowspan="2">Lokalak</th>
            </tr>
            <tr class="subheader">
                <th>Izen Osoa</th>
                <th>Email / Telefonoa</th>
                <th>Izen Osoa</th>
                <th>Email / Telefonoa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($owners as $owner)
                @php
                    $portals = $owner->assignments
                        ->filter(
                            fn($assignment) => $assignment->property?->location?->type === 'portal',
                        )
                        ->values();
                    $locals = $owner->assignments
                        ->filter(
                            fn($assignment) => $assignment->property?->location?->type === 'local',
                        )
                        ->values();
                    $garages = $owner->assignments
                        ->filter(
                            fn($assignment) => $assignment->property?->location?->type === 'garage',
                        )
                        ->values();
                    $storages = $owner->assignments
                        ->filter(
                            fn($assignment) => $assignment->property?->location?->type ===
                                'storage',
                        )
                        ->values();
                    $assignmentGroups = [$portals, $garages, $storages, $locals];
                @endphp
                <tr>
                    <td class="owner-id">{{ $owner->id }} <br> [{{ $owner->language }}]</td>
                    <td>{{ trim(($owner->coprop1_name ?? '') . ' ' . ($owner->coprop1_surname ?? '')) }}
                    </td>
                    <td>
                        @if (($owner->coprop1_email ?? '') !== '' || ($owner->coprop1_phone ?? '') !== '')
                            @if (($owner->coprop1_email ?? '') !== '')
                                <div
                                    class="{{ (bool) ($owner->coprop1_email_invalid ?? false) ? 'invalid-contact' : '' }}">
                                    {{ $owner->coprop1_email }}</div>
                            @endif
                            @if (($owner->coprop1_phone ?? '') !== '')
                                <div
                                    class="contact-line nowrap {{ (bool) ($owner->coprop1_phone_invalid ?? false) ? 'invalid-contact' : '' }}">
                                    {{ $owner->coprop1_phone }}
                                    @if ((bool) $owner->coprop1_has_whatsapp)
                                        <span class="whatsapp-check">&#10003;</span>
                                    @endif
                                </div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ trim(($owner->coprop2_name ?? '') . ' ' . ($owner->coprop2_surname ?? '')) ?: '-' }}
                    </td>
                    <td>
                        @if (($owner->coprop2_email ?? '') !== '' || ($owner->coprop2_phone ?? '') !== '')
                            @if (($owner->coprop2_email ?? '') !== '')
                                <div
                                    class="{{ (bool) ($owner->coprop2_email_invalid ?? false) ? 'invalid-contact' : '' }}">
                                    {{ $owner->coprop2_email }}</div>
                            @endif
                            @if (($owner->coprop2_phone ?? '') !== '')
                                <div
                                    class="contact-line nowrap {{ (bool) ($owner->coprop2_phone_invalid ?? false) ? 'invalid-contact' : '' }}">
                                    {{ $owner->coprop2_phone }}
                                    @if ((bool) $owner->coprop2_has_whatsapp)
                                        <span class="whatsapp-check">&#10003;</span>
                                    @endif
                                </div>
                            @endif
                        @else
                            -
                        @endif
                    </td>

                    @foreach ($assignmentGroups as $assignmentGroup)
                        <td>
                            @forelse ($assignmentGroup as $assignment)
                                <div class="line">
                                    <strong>
                                        {{ trim(($assignment->property->location->code ?? '') . ' ' . ($assignment->property->name ?? '')) }}
                                    </strong>
                                    <br>
                                    <span class="nowrap">
                                        {{ $assignment->property->location_pct !== null ? number_format((float) $assignment->property->location_pct, 2, ',', '.') . '%' : '-' }}
                                        |
                                        {{ $assignment->property->community_pct !== null ? number_format((float) $assignment->property->community_pct, 2, ',', '.') . '%' : '-' }}
                                    </span>
                                </div>
                            @empty
                                <div class="empty">-</div>
                            @endforelse
                        </td>
                    @endforeach
                </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">{{ __('admin.owners.no_records') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>

    </html>
