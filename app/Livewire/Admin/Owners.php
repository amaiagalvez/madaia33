<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOwnerAction;
use App\Actions\AssignPropertyAction;
use App\Actions\UnassignPropertyAction;
use App\Models\Owner;
use Livewire\Component;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class Owners extends Component
{
    use WithPagination;

    private CreateOwnerAction $createOwnerAction;

    private AssignPropertyAction $assignPropertyAction;

    private UnassignPropertyAction $unassignPropertyAction;

    public bool $showCreateForm = false;

    public string $coprop1Name = '';

    public string $coprop1Dni = '';

    public string $coprop1Phone = '';

    public string $coprop1Email = '';

    public string $coprop2Name = '';

    public string $coprop2Dni = '';

    public string $coprop2Phone = '';

    public string $coprop2Email = '';

    public string $filterStatus = 'active';

    public string $filterPortal = '';

    public string $filterGarage = '';

    public string $filterStorage = '';

    public string $ownershipView = 'default';

    /**
     * @var array<int, array{property_id: string, start_date: string, end_date: string}>
     */
    public array $newAssignments = [];

    public ?int $expandedOwnerId = null;

    /**
     * @var array<int, array{start_date: string, end_date: string, admin_validated: bool, owner_validated: bool}>
     */
    public array $assignmentEdits = [];

    public string $inlinePropertyId = '';

    public string $inlineStartDate = '';

    public string $inlineEndDate = '';

    public string $rowErrorMessage = '';

    public function boot(
        CreateOwnerAction $createOwnerAction,
        AssignPropertyAction $assignPropertyAction,
        UnassignPropertyAction $unassignPropertyAction,
    ): void {
        $this->createOwnerAction = $createOwnerAction;
        $this->assignPropertyAction = $assignPropertyAction;
        $this->unassignPropertyAction = $unassignPropertyAction;
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->ownershipView = 'default';
    }

    public function updatedFilterPortal(): void
    {
        $this->resetPage();
    }

    public function updatedFilterGarage(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStorage(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->newAssignments = [$this->newAssignmentRow()];
    }

    /**
     * @return array{property_id: string, start_date: string, end_date: string}
     */
    private function newAssignmentRow(): array
    {
        return [
            'property_id' => '',
            'start_date' => '',
            'end_date' => '',
        ];
    }

    public function showWithoutProperties(): void
    {
        $this->filterStatus = 'inactive';
        $this->ownershipView = 'without_properties';
        $this->resetPage();
    }

    public function clearWithoutProperties(): void
    {
        $this->ownershipView = 'default';
        $this->filterStatus = 'all';
        $this->resetPage();
    }

    public function addAssignmentRow(): void
    {
        $this->newAssignments[] = $this->newAssignmentRow();
    }

    public function removeAssignmentRow(int $index): void
    {
        if (count($this->newAssignments) === 1) {
            $this->newAssignments = [$this->newAssignmentRow()];

            return;
        }

        unset($this->newAssignments[$index]);
        $this->newAssignments = array_values($this->newAssignments);
    }

    public function createOwner(): void
    {
        $data = $this->validate([
            'coprop1Name' => ['required', 'string', 'max:255'],
            'coprop1Dni' => ['required', 'string', 'max:20', 'unique:owners,coprop1_dni'],
            'coprop1Phone' => ['nullable', 'string', 'max:20'],
            'coprop1Email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'coprop2Name' => ['nullable', 'string', 'max:255'],
            'coprop2Dni' => ['nullable', 'string', 'max:20'],
            'coprop2Phone' => ['nullable', 'string', 'max:20'],
            'coprop2Email' => ['nullable', 'email', 'max:255'],
            'newAssignments' => ['required', 'array', 'min:1'],
            'newAssignments.*.property_id' => ['required', 'exists:properties,id'],
            'newAssignments.*.start_date' => ['required', 'date'],
            'newAssignments.*.end_date' => ['nullable', 'date'],
        ], [
            'newAssignments.*.property_id.required' => __('validation.required', ['attribute' => __('admin.owners.property')]),
            'newAssignments.*.property_id.exists' => __('validation.exists', ['attribute' => __('admin.owners.property')]),
            'newAssignments.*.start_date.required' => __('validation.required', ['attribute' => __('admin.owners.start_date')]),
            'newAssignments.*.start_date.date' => __('validation.date', ['attribute' => __('admin.owners.start_date')]),
            'newAssignments.*.end_date.date' => __('validation.date', ['attribute' => __('admin.owners.end_date')]),
        ], [
            'coprop1Name' => __('admin.owners.form.coprop1_name'),
            'coprop1Dni' => __('admin.owners.form.coprop1_dni'),
            'coprop1Phone' => __('admin.owners.form.coprop1_phone'),
            'coprop1Email' => __('admin.owners.form.coprop1_email'),
            'coprop2Name' => __('admin.owners.form.coprop2_name'),
            'coprop2Dni' => __('admin.owners.form.coprop2_dni'),
            'coprop2Phone' => __('admin.owners.form.coprop2_phone'),
            'coprop2Email' => __('admin.owners.form.coprop2_email'),
            'newAssignments.*.property_id' => __('admin.owners.property'),
            'newAssignments.*.start_date' => __('admin.owners.start_date'),
            'newAssignments.*.end_date' => __('admin.owners.end_date'),
        ]);

        foreach ($data['newAssignments'] as $index => $assignment) {
            if ($assignment['end_date'] !== '' && $assignment['end_date'] !== null && $assignment['end_date'] < $assignment['start_date']) {
                $this->addError("newAssignments.$index.end_date", __('validation.after_or_equal', ['attribute' => __('admin.owners.end_date'), 'date' => __('admin.owners.start_date')]));

                return;
            }
        }

        $this->createOwnerAction->execute([
            'coprop1_name' => $data['coprop1Name'],
            'coprop1_dni' => $data['coprop1Dni'],
            'coprop1_phone' => $data['coprop1Phone'] ?: null,
            'coprop1_email' => $data['coprop1Email'],
            'coprop2_name' => $data['coprop2Name'] ?: null,
            'coprop2_dni' => $data['coprop2Dni'] ?: null,
            'coprop2_phone' => $data['coprop2Phone'] ?: null,
            'coprop2_email' => $data['coprop2Email'] ?: null,
            'assignments' => collect($data['newAssignments'])->map(static function (array $assignment): array {
                return [
                    'property_id' => (int) $assignment['property_id'],
                    'start_date' => $assignment['start_date'],
                    'end_date' => $assignment['end_date'] !== '' ? $assignment['end_date'] : null,
                ];
            })->all(),
        ]);

        $this->reset([
            'coprop1Name',
            'coprop1Dni',
            'coprop1Phone',
            'coprop1Email',
            'coprop2Name',
            'coprop2Dni',
            'coprop2Phone',
            'coprop2Email',
        ]);
        $this->newAssignments = [$this->newAssignmentRow()];

        $this->showCreateForm = false;
        $this->resetPage();
    }

    public function toggleOwnerRow(int $ownerId): void
    {
        if ($this->expandedOwnerId === $ownerId) {
            $this->expandedOwnerId = null;
            $this->assignmentEdits = [];
            $this->inlinePropertyId = '';
            $this->inlineStartDate = '';
            $this->inlineEndDate = '';
            $this->rowErrorMessage = '';

            return;
        }

        $this->expandedOwnerId = $ownerId;
        $this->rowErrorMessage = '';
        $this->inlinePropertyId = '';
        $this->inlineStartDate = now()->format('Y-m-d');
        $this->inlineEndDate = '';

        $this->loadAssignmentEdits($ownerId);
    }

    public function saveAssignment(int $assignmentId): void
    {
        $assignment = PropertyAssignment::query()
            ->where('owner_id', $this->expandedOwnerId)
            ->findOrFail($assignmentId);

        $edit = $this->assignmentEdits[$assignmentId] ?? null;

        if ($edit === null) {
            return;
        }

        $this->validate([
            "assignmentEdits.$assignmentId.start_date" => ['required', 'date'],
            "assignmentEdits.$assignmentId.end_date" => ['nullable', 'date'],
        ]);

        if ($edit['end_date'] !== '' && $edit['end_date'] < $edit['start_date']) {
            $this->addError("assignmentEdits.$assignmentId.end_date", __('validation.after_or_equal', ['attribute' => __('admin.owners.end_date'), 'date' => __('admin.owners.start_date')]));

            return;
        }

        $isClosingAssignment = $edit['end_date'] !== '';
        $isAlreadyClosed = $assignment->end_date !== null;
        $willBeClosed = $isAlreadyClosed || $isClosingAssignment;

        $assignment->update([
            'start_date' => $edit['start_date'],
            'admin_validated' => $willBeClosed ? $assignment->admin_validated : (bool) $edit['admin_validated'],
            'owner_validated' => $willBeClosed ? $assignment->owner_validated : (bool) $edit['owner_validated'],
        ]);

        if (! $isAlreadyClosed && $isClosingAssignment) {
            try {
                $this->unassignPropertyAction->execute($assignment, $edit['end_date']);
            } catch (ValidationException $e) {
                $this->rowErrorMessage = (string) collect($e->errors())->flatten()->first();

                return;
            }
        } elseif (! $isClosingAssignment) {
            $assignment->update(['end_date' => null]);
        }

        $this->loadAssignmentEdits((int) $assignment->owner_id);
    }

    public function createInlineAssignment(): void
    {
        if ($this->expandedOwnerId === null) {
            return;
        }

        $validated = $this->validate([
            'inlinePropertyId' => ['required', 'exists:properties,id'],
            'inlineStartDate' => ['required', 'date'],
            'inlineEndDate' => ['nullable', 'date'],
        ]);

        if ($validated['inlineEndDate'] !== '' && $validated['inlineEndDate'] < $validated['inlineStartDate']) {
            $this->addError('inlineEndDate', __('validation.after_or_equal', ['attribute' => __('admin.owners.end_date'), 'date' => __('admin.owners.start_date')]));

            return;
        }

        $owner = Owner::findOrFail($this->expandedOwnerId);
        $property = Property::findOrFail((int) $validated['inlinePropertyId']);

        try {
            if ($validated['inlineEndDate'] === '') {
                $this->assignPropertyAction->execute($property, $owner, $validated['inlineStartDate']);
            } else {
                PropertyAssignment::create([
                    'owner_id' => $owner->id,
                    'property_id' => $property->id,
                    'start_date' => $validated['inlineStartDate'],
                    'end_date' => $validated['inlineEndDate'],
                    'admin_validated' => false,
                    'owner_validated' => false,
                ]);
            }

            $this->inlinePropertyId = '';
            $this->inlineStartDate = now()->format('Y-m-d');
            $this->inlineEndDate = '';
            $this->rowErrorMessage = '';

            $this->loadAssignmentEdits($owner->id);
        } catch (ValidationException $e) {
            $this->rowErrorMessage = (string) collect($e->errors())->flatten()->first();
        }
    }

    private function loadAssignmentEdits(int $ownerId): void
    {
        $assignments = PropertyAssignment::query()
            ->where('owner_id', $ownerId)
            ->orderByRaw('end_date IS NULL DESC')
            ->orderBy('start_date', 'desc')
            ->get();

        $this->assignmentEdits = [];

        foreach ($assignments as $assignment) {
            $this->assignmentEdits[$assignment->id] = [
                'start_date' => optional($assignment->start_date)->format('Y-m-d') ?? '',
                'end_date' => optional($assignment->end_date)->format('Y-m-d') ?? '',
                'admin_validated' => (bool) $assignment->admin_validated,
                'owner_validated' => (bool) $assignment->owner_validated,
            ];
        }
    }

    public function cancelCreateOwner(): void
    {
        $this->reset([
            'coprop1Name',
            'coprop1Dni',
            'coprop1Phone',
            'coprop1Email',
            'coprop2Name',
            'coprop2Dni',
            'coprop2Phone',
            'coprop2Email',
        ]);

        $this->newAssignments = [$this->newAssignmentRow()];

        $this->resetValidation();
        $this->showCreateForm = false;
    }

    public function render(): View
    {
        $query = Owner::with([
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

        if ($this->filterPortal !== '') {
            $query->whereHas('activeAssignments.property.location', function (Builder $q) {
                $q->where('type', 'portal')->where('id', $this->filterPortal);
            });
        }

        if ($this->filterGarage !== '') {
            $query->whereHas('activeAssignments.property.location', function (Builder $q) {
                $q->where('type', 'garage')->where('id', $this->filterGarage);
            });
        }

        if ($this->filterStorage !== '') {
            $query->whereHas('activeAssignments.property.location', function (Builder $q) {
                $q->where('type', 'storage')->where('id', $this->filterStorage);
            });
        }

        $owners = $query->orderBy('coprop1_name')->paginate(20);

        $portals = Location::portals()->orderBy('code')->get();
        $garages = Location::garages()->orderBy('code')->get();
        $storages = Location::storage()->orderBy('code')->get();
        $assignableProperties = Property::query()
            ->with('location')
            ->orderBy('location_id')
            ->orderBy('name')
            ->get();

        $expandedAssignments = collect();

        if ($this->expandedOwnerId !== null) {
            $expandedAssignments = PropertyAssignment::query()
                ->with('property.location')
                ->where('owner_id', $this->expandedOwnerId)
                ->orderByRaw('end_date IS NULL DESC')
                ->orderBy('start_date', 'desc')
                ->get();
        }

        return view('livewire.admin.owners.index', [
            'owners' => $owners,
            'portals' => $portals,
            'garages' => $garages,
            'storages' => $storages,
            'assignableProperties' => $assignableProperties,
            'expandedAssignments' => $expandedAssignments,
        ]);
    }
}
