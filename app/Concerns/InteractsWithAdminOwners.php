<?php

namespace App\Concerns;

use App\Models\Owner;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithAdminOwners
{
    /**
     * @return array<string, array<int, string>>
     */
    private function ownerCreationRules(): array
    {
        return [
            'coprop1Name' => ['required', 'string', 'max:255'],
            'coprop1Dni' => ['required', 'string', 'max:20', 'unique:owners,coprop1_dni'],
            'coprop1Phone' => ['nullable', 'string', 'max:20'],
            'coprop1Email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'language' => ['required', 'string', 'in:eu,es'],
            'coprop2Name' => ['nullable', 'string', 'max:255'],
            'coprop2Dni' => ['nullable', 'string', 'max:20'],
            'coprop2Phone' => ['nullable', 'string', 'max:20'],
            'coprop2Email' => ['nullable', 'email', 'max:255'],
            'newAssignments' => ['required', 'array', 'min:1'],
            'newAssignments.*.property_id' => ['required', 'exists:properties,id'],
            'newAssignments.*.start_date' => ['required', 'date'],
            'newAssignments.*.end_date' => ['nullable', 'date'],
        ];
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
            'coprop1Dni' => __('admin.owners.form.coprop1_dni'),
            'coprop1Phone' => __('admin.owners.form.coprop1_phone'),
            'coprop1Email' => __('admin.owners.form.coprop1_email'),
            'language' => __('admin.owners.form.language'),
            'coprop2Name' => __('admin.owners.form.coprop2_name'),
            'coprop2Dni' => __('admin.owners.form.coprop2_dni'),
            'coprop2Phone' => __('admin.owners.form.coprop2_phone'),
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
            'coprop1Dni',
            'coprop1Phone',
            'coprop1Email',
            'language',
            'coprop2Name',
            'coprop2Dni',
            'coprop2Phone',
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

        if ($this->filterStatus === 'active') {
            $query->whereHas('activeAssignments');
        } elseif ($this->filterStatus === 'inactive') {
            $query->whereDoesntHave('activeAssignments');
        }

        if ($this->ownershipView === 'without_properties') {
            $query->whereDoesntHave('activeAssignments');
        }

        $this->applyLocationFilter($query, $this->filterPortal, 'portal');
        $this->applyLocationFilter($query, $this->filterLocal, 'local');
        $this->applyLocationFilter($query, $this->filterGarage, 'garage');
        $this->applyLocationFilter($query, $this->filterStorage, 'storage');

        return $query;
    }

    /**
     * @return array{portals: Collection<int, Location>, locals: Collection<int, Location>, garages: Collection<int, Location>, storages: Collection<int, Location>, assignableProperties: Collection<int, Property>}
     */
    private function loadViewData(): array
    {
        return [
            'portals' => Location::portals()->orderBy('code')->get(),
            'locals' => Location::locals()->orderBy('code')->get(),
            'garages' => Location::garages()->orderBy('code')->get(),
            'storages' => Location::storage()->orderBy('code')->get(),
            'assignableProperties' => Property::query()
                ->with('location')
                ->orderBy('location_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * @param  Builder<Owner>  $query
     */
    private function applyLocationFilter(Builder $query, string $locationId, string $type): void
    {
        if ($locationId === '') {
            return;
        }

        $query->whereHas('activeAssignments.property.location', function (Builder $locationQuery) use ($locationId, $type): void {
            $locationQuery->where('type', $type)->where('id', $locationId);
        });
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
