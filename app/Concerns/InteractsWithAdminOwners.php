<?php

namespace App\Concerns;

use App\Models\Owner;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
use App\Support\AdminOwnersFilters;
use App\Validations\OwnerFormValidation;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithAdminOwners
{
    /**
     * @return array<string, mixed>
     */
    private function ownerCreationRules(): array
    {
        return OwnerFormValidation::createRules();
    }

    /**
     * @return array<string, string>
     */
    private function ownerCreationMessages(): array
    {
        return [
            'newAssignments.*.property_id.required' => __('validation.required', ['attribute' => __('admin.owners.property')]),
            'newAssignments.*.property_id.exists' => __('validation.exists', ['attribute' => __('admin.owners.property')]),
            'newAssignments.*.start_date.required' => __('validation.required', ['attribute' => __('admin.owners.start_date')]),
            'newAssignments.*.start_date.date' => __('validation.date', ['attribute' => __('admin.owners.start_date')]),
            'newAssignments.*.end_date.date' => __('validation.date', ['attribute' => __('admin.owners.end_date')]),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function ownerCreationAttributes(): array
    {
        return [
            'coprop1Name' => __('admin.owners.form.coprop1_name'),
            'coprop1Surname' => __('admin.owners.form.coprop1_surname'),
            'coprop1Dni' => __('admin.owners.form.coprop1_dni'),
            'coprop1Phone' => __('admin.owners.form.coprop1_phone'),
            'coprop1HasWhatsapp' => __('admin.owners.form.has_whatsapp'),
            'coprop1Email' => __('admin.owners.form.coprop1_email'),
            'language' => __('admin.owners.form.language'),
            'coprop2Name' => __('admin.owners.form.coprop2_name'),
            'coprop2Surname' => __('admin.owners.form.coprop2_surname'),
            'coprop2Dni' => __('admin.owners.form.coprop2_dni'),
            'coprop2Phone' => __('admin.owners.form.coprop2_phone'),
            'coprop2HasWhatsapp' => __('admin.owners.form.has_whatsapp'),
            'coprop2Email' => __('admin.owners.form.coprop2_email'),
            'newAssignments.*.property_id' => __('admin.owners.property'),
            'newAssignments.*.start_date' => __('admin.owners.start_date'),
            'newAssignments.*.end_date' => __('admin.owners.end_date'),
        ];
    }

    private function resetCreateOwnerFormState(): void
    {
        $this->reset([
            'coprop1Name',
            'coprop1Surname',
            'coprop1Dni',
            'coprop1Phone',
            'coprop1HasWhatsapp',
            'coprop1Email',
            'language',
            'coprop2Name',
            'coprop2Surname',
            'coprop2Dni',
            'coprop2Phone',
            'coprop2HasWhatsapp',
            'coprop2Email',
        ]);

        $this->newAssignments = [$this->newAssignmentRow()];
        $this->showCreateForm = false;
        $this->resetPage();
    }

    /**
     * @return Builder<Owner>
     */
    private function buildOwnersQuery(): Builder
    {
        $query = Owner::query()->with([
            'user',
            'activeAssignments.property.location',
            'assignments.property.location',
        ]);

        $query->addSelect([
            'portal_location_sort' => PropertyAssignment::query()
                ->select('locations.name')
                ->join('properties', 'properties.id', '=', 'property_assignments.property_id')
                ->join('locations', 'locations.id', '=', 'properties.location_id')
                ->whereColumn('property_assignments.owner_id', 'owners.id')
                ->where('locations.type', 'portal')
                ->whereNull('property_assignments.end_date')
                ->orderBy('locations.name')
                ->orderBy('properties.code')
                ->orderBy('properties.name')
                ->limit(1),
            'portal_property_sort' => PropertyAssignment::query()
                ->selectRaw('COALESCE(properties.code, properties.name)')
                ->join('properties', 'properties.id', '=', 'property_assignments.property_id')
                ->join('locations', 'locations.id', '=', 'properties.location_id')
                ->whereColumn('property_assignments.owner_id', 'owners.id')
                ->where('locations.type', 'portal')
                ->whereNull('property_assignments.end_date')
                ->orderBy('locations.name')
                ->orderBy('properties.code')
                ->orderBy('properties.name')
                ->limit(1),
        ]);

        AdminOwnersFilters::apply($query, [
            'status' => $this->filterStatus,
            'portal' => $this->filterPortal,
            'local' => $this->filterLocal,
            'garage' => $this->filterGarage,
            'storage' => $this->filterStorage,
            'search' => $this->filterSearch,
            'ownershipView' => $this->ownershipView,
        ]);

        return $query
            ->orderByRaw('portal_location_sort IS NULL ASC')
            ->orderBy('portal_location_sort')
            ->orderBy('portal_property_sort')
            ->orderBy('owners.id');
    }

    /**
     * @return array{portals: Collection<int, Location>, locals: Collection<int, Location>, garages: Collection<int, Location>, storages: Collection<int, Location>, assignableProperties: Collection<int, Property>}
     */
    private function loadViewData(): array
    {
        return [
            'portals' => Location::portals()->orderBy('name')->get(),
            'locals' => Location::locals()->orderBy('name')->get(),
            'garages' => Location::garages()->orderBy('name')->get(),
            'storages' => Location::storage()->orderBy('name')->get(),
            'assignableProperties' => Property::query()
                ->with('location')
                ->whereDoesntHave('activeAssignments')
                ->orderBy('location_id')
                ->orderBy('code')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function loadExpandedAssignments(): mixed
    {
        if ($this->expandedOwnerId === null) {
            return collect();
        }

        return PropertyAssignment::query()
            ->with('property.location')
            ->where('owner_id', $this->expandedOwnerId)
            ->orderByRaw('end_date IS NULL DESC')
            ->orderBy('start_date', 'desc')
            ->get();
    }
}
