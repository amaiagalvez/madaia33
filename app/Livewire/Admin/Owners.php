<?php

namespace App\Livewire\Admin;

use App\Models\Owner;
use Livewire\Component;
use App\Models\Property;
use App\SupportedLocales;
use Livewire\WithPagination;
use App\Models\PropertyAssignment;
use Illuminate\Contracts\View\View;
use App\Services\CreateOwnerFormService;
use App\Actions\Owners\CreateOwnerAction;
use App\Concerns\InteractsWithAdminOwners;
use Illuminate\Validation\ValidationException;
use App\Actions\Properties\AssignPropertyAction;
use App\Actions\Properties\UnassignPropertyAction;

class Owners extends Component
{
    use InteractsWithAdminOwners;
    use WithPagination;

    private CreateOwnerAction $createOwnerAction;

    private CreateOwnerFormService $createOwnerFormService;

    private AssignPropertyAction $assignPropertyAction;

    private UnassignPropertyAction $unassignPropertyAction;

    public bool $showCreateForm = false;

    public string $coprop1Name = '';

    public string $coprop1Dni = '';

    public string $coprop1Phone = '';

    public string $coprop1Email = '';

    public string $language = SupportedLocales::BASQUE;

    public string $coprop2Name = '';

    public string $coprop2Dni = '';

    public string $coprop2Phone = '';

    public string $coprop2Email = '';

    public string $filterStatus = 'active';

    public string $filterPortal = '';

    public string $filterLocal = '';

    public string $filterGarage = '';

    public string $filterStorage = '';

    public string $ownershipView = 'default';

    // Edit owner slideover
    public bool $showEditOwnerForm = false;

    public ?int $editingOwnerId = null;

    public string $editCoprop1Name = '';

    public string $editCoprop1Dni = '';

    public string $editCoprop1Phone = '';

    public string $editCoprop1Email = '';

    public string $editLanguage = SupportedLocales::BASQUE;

    public string $editCoprop2Name = '';

    public string $editCoprop2Dni = '';

    public string $editCoprop2Phone = '';

    public string $editCoprop2Email = '';

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
        CreateOwnerFormService $createOwnerFormService,
        AssignPropertyAction $assignPropertyAction,
        UnassignPropertyAction $unassignPropertyAction,
    ): void {
        $this->createOwnerAction = $createOwnerAction;
        $this->createOwnerFormService = $createOwnerFormService;
        $this->assignPropertyAction = $assignPropertyAction;
        $this->unassignPropertyAction = $unassignPropertyAction;
    }

    /**
     * Reset pagination when any filter changes
     * Handling filterStatus separately to also reset ownershipView
     */
    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->ownershipView = 'default';
    }

    public function updatedFilterPortal(): void
    {
        $this->resetPage();
    }

    public function updatedFilterLocal(): void
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

        $editOwnerId = (int) request()->integer('editOwner');

        if ($editOwnerId > 0) {
            $this->openEditOwnerForm($editOwnerId);
        }
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
        $data = $this->validate(
            $this->ownerCreationRules(),
            $this->ownerCreationMessages(),
            $this->ownerCreationAttributes(),
        );

        $dateErrors = $this->createOwnerFormService->validateAssignmentDates($data['newAssignments']);

        if ($dateErrors !== []) {
            foreach ($dateErrors as $field => $message) {
                $this->addError($field, $message);
            }

            return;
        }

        $this->createOwnerAction->execute($this->createOwnerFormService->prepareOwnerData($data));
        $this->resetCreateOwnerFormState();
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

    public function openEditOwnerForm(int $ownerId): void
    {
        $owner = Owner::findOrFail($ownerId);

        $this->editingOwnerId = $ownerId;
        $this->editCoprop1Name = $owner->coprop1_name;
        $this->editCoprop1Dni = $owner->coprop1_dni;
        $this->editCoprop1Phone = $owner->coprop1_phone ?? '';
        $this->editCoprop1Email = $owner->coprop1_email;
        $this->editLanguage = $owner->language ?? SupportedLocales::BASQUE;
        $this->editCoprop2Name = $owner->coprop2_name ?? '';
        $this->editCoprop2Dni = $owner->coprop2_dni ?? '';
        $this->editCoprop2Phone = $owner->coprop2_phone ?? '';
        $this->editCoprop2Email = $owner->coprop2_email ?? '';
        $this->resetValidation();
        $this->showEditOwnerForm = true;
    }

    public function saveEditOwner(): void
    {
        $this->validate([
            'editCoprop1Name' => ['required', 'string', 'max:255'],
            'editCoprop1Dni' => ['required', 'string', 'max:20'],
            'editCoprop1Phone' => ['nullable', 'string', 'max:20'],
            'editCoprop1Email' => ['required', 'email', 'max:255'],
            'editLanguage' => ['required', 'string', 'in:eu,es'],
            'editCoprop2Name' => ['nullable', 'string', 'max:255'],
            'editCoprop2Dni' => ['nullable', 'string', 'max:20'],
            'editCoprop2Phone' => ['nullable', 'string', 'max:20'],
            'editCoprop2Email' => ['nullable', 'email', 'max:255'],
        ], [], [
            'editCoprop1Name' => __('admin.owners.form.coprop1_name'),
            'editCoprop1Dni' => __('admin.owners.form.coprop1_dni'),
            'editCoprop1Phone' => __('admin.owners.form.coprop1_phone'),
            'editCoprop1Email' => __('admin.owners.form.coprop1_email'),
            'editLanguage' => __('admin.owners.form.language'),
            'editCoprop2Name' => __('admin.owners.form.coprop2_name'),
            'editCoprop2Dni' => __('admin.owners.form.coprop2_dni'),
            'editCoprop2Phone' => __('admin.owners.form.coprop2_phone'),
            'editCoprop2Email' => __('admin.owners.form.coprop2_email'),
        ]);

        $owner = Owner::findOrFail((int) $this->editingOwnerId);

        $owner->update([
            'coprop1_name' => $this->editCoprop1Name,
            'coprop1_dni' => $this->editCoprop1Dni,
            'coprop1_phone' => $this->editCoprop1Phone ?: null,
            'coprop1_email' => $this->editCoprop1Email,
            'language' => $this->editLanguage,
            'coprop2_name' => $this->editCoprop2Name ?: null,
            'coprop2_dni' => $this->editCoprop2Dni ?: null,
            'coprop2_phone' => $this->editCoprop2Phone ?: null,
            'coprop2_email' => $this->editCoprop2Email ?: null,
        ]);

        $this->cancelEditOwner();
    }

    public function cancelEditOwner(): void
    {
        $this->showEditOwnerForm = false;
        $this->editingOwnerId = null;
        $this->reset([
            'editCoprop1Name',
            'editCoprop1Dni',
            'editCoprop1Phone',
            'editCoprop1Email',
            'editLanguage',
            'editCoprop2Name',
            'editCoprop2Dni',
            'editCoprop2Phone',
            'editCoprop2Email',
        ]);
        $this->resetValidation();
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
        return view('livewire.admin.owners.index', [
            'owners' => $this->buildOwnersQuery()->orderBy('coprop1_name')->paginate(20),
            ...$this->loadViewData(),
            'expandedAssignments' => $this->loadExpandedAssignments(),
        ]);
    }
}
