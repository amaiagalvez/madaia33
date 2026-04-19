<?php

namespace App\Http\Controllers\Admin;

use App\Models\Owner;
use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\AdminOwnersFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;

class OwnersPdfController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
    $filters = [
        'status' => (string) $request->query('filter_status', 'active'),
        'portal' => (string) $request->query('filter_portal', ''),
        'local' => (string) $request->query('filter_local', ''),
        'garage' => (string) $request->query('filter_garage', ''),
        'storage' => (string) $request->query('filter_storage', ''),
        'search' => (string) $request->query('filter_search', ''),
        'ownershipView' => (string) $request->query('ownership_view', 'default'),
    ];

    $query = Owner::query()->with([
        'user',
        'activeAssignments.property.location',
        'assignments.property.location',
    ]);

    AdminOwnersFilters::apply($query, $filters);

    $owners = $query->orderBy('id')->get();

    $pdf = Pdf::loadView('pdf.owners.list', [
        'owners' => $owners,
        'appliedFilters' => $this->buildAppliedFilters($filters),
    ])->setPaper('a4', 'landscape');

    $baseName = (string) __('admin.owners.pdf.filename');
    $slug = Str::slug($baseName);

    if ($slug === '') {
        $slug = 'owners';
    }

    $filename = sprintf('%s-%s.pdf', $slug, now()->format('Ymd-His'));

    return response()->streamDownload(
        static function () use ($pdf): void {
        echo $pdf->output();
        },
        $filename,
        ['Content-Type' => 'application/pdf']
    );
    }

  /**
   * @param  array{status: string, portal: string, local: string, garage: string, storage: string, search: string, ownershipView: string}  $filters
   * @return array<int, string>
   */
    private function buildAppliedFilters(array $filters): array
    {
    $appliedFilters = [];

    if ($filters['status'] !== 'active') {
        $appliedFilters[] = __('admin.owners.filters.status_pdf') . ': ' . $this->statusLabel($filters['status']);
    }

    if ($filters['ownershipView'] === 'without_properties') {
        $appliedFilters[] = __('admin.owners.filters.without_properties');
    }

    if (trim($filters['search']) !== '') {
        $appliedFilters[] = __('admin.owners.filters.search') . ': ' . trim($filters['search']);
    }

    $locationLabels = $this->locationFilterLabels($filters);

    foreach ($locationLabels as $locationLabel) {
        $appliedFilters[] = $locationLabel;
    }

    return $appliedFilters;
    }

    private function statusLabel(string $status): string
    {
    return match ($status) {
        'inactive' => (string) __('admin.owners.filters.inactive'),
        'all' => (string) __('admin.owners.filters.all'),
        default => (string) __('admin.owners.filters.active'),
    };
    }

  /**
   * @param  array{status: string, portal: string, local: string, garage: string, storage: string, search: string, ownershipView: string}  $filters
   * @return array<int, string>
   */
    private function locationFilterLabels(array $filters): array
    {
    $selectedLocationIds = collect([
        $filters['portal'],
        $filters['local'],
        $filters['garage'],
        $filters['storage'],
    ])
        ->filter(static fn(string $id): bool => $id !== '')
        ->map(static fn(string $id): int => (int) $id)
        ->filter(static fn(int $id): bool => $id > 0)
        ->unique()
        ->values();

    if ($selectedLocationIds->isEmpty()) {
        return [];
    }

    /** @var Collection<int, Location> $locations */
    $locations = Location::query()
        ->whereIn('id', $selectedLocationIds->all())
        ->get(['id', 'code', 'type'])
        ->keyBy('id');

    $labels = [];

    foreach (['portal', 'local', 'garage', 'storage'] as $type) {
        $locationId = (int) $filters[$type];

        if ($locationId <= 0) {
        continue;
        }

        $location = $locations->get($locationId);

        if (! $location instanceof Location) {
        continue;
        }

        $labels[] = __('admin.owners.filters.' . $type . '_pdf') . ': ' . $location->code;
    }

    return $labels;
    }
}
