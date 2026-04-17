<?php

namespace App\Concerns;

use App\Models\Owner;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
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
            'ownerId' => __('admin.owners.form.id'),
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
            'ownerId',
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
        $this->applySearchFilter($query, $this->filterSearch);

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
                ->whereDoesntHave('activeAssignments')
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

    /**
     * @param  Builder<Owner>  $query
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $term = trim($search);

        if ($term === '') {
            return;
        }

        $escapedTerm = addcslashes($term, '%_');

        $query->where(function (Builder $searchQuery) use ($escapedTerm, $term): void {
            $like = '%' . $escapedTerm . '%';

            $searchQuery
                ->where('coprop1_name', 'like', $like)
                ->orWhere('coprop1_surname', 'like', $like)
                ->orWhere('coprop1_dni', 'like', $like)
                ->orWhere('coprop1_phone', 'like', $like)
                ->orWhere('coprop1_email', 'like', $like)
                ->orWhere('coprop2_name', 'like', $like)
                ->orWhere('coprop2_surname', 'like', $like)
                ->orWhere('coprop2_dni', 'like', $like)
                ->orWhere('coprop2_phone', 'like', $like)
                ->orWhere('coprop2_email', 'like', $like)
                ->orWhere('language', 'like', $like);

            if (is_numeric($term)) {
                $searchQuery->orWhere('id', (int) $term);
            }
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
