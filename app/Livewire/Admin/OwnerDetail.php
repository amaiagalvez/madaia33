<?php

namespace App\Livewire\Admin;

use App\Models\Owner;
use App\Models\Property;
use Livewire\Component;
use App\Models\PropertyAssignment;
use Illuminate\Contracts\View\View;
use App\Actions\AssignPropertyAction;
use App\Actions\UnassignPropertyAction;
use App\Actions\DeactivateOwnerAction;
use Illuminate\Validation\ValidationException;

class OwnerDetail extends Component
{
    private AssignPropertyAction $assignPropertyAction;

    private UnassignPropertyAction $unassignPropertyAction;

    private DeactivateOwnerAction $deactivateOwnerAction;

    public Owner $owner;

  // Personal data editing
    public bool $showEditForm = false;

    public string $coprop1Name = '';

    public string $coprop1Dni = '';

    public string $coprop1Phone = '';

    public string $coprop1Email = '';

    public string $coprop2Name = '';

    public string $coprop2Dni = '';

    public string $coprop2Phone = '';

    public string $coprop2Email = '';

  // Assignment management
    public bool $showAssignForm = false;

    public string $assignPropertyId = '';

    public string $assignStartDate = '';

  // Unassign
    public ?int $unassigningId = null;

    public string $unassignEndDate = '';

    public string $errorMessage = '';

    public function boot(
        AssignPropertyAction $assignPropertyAction,
        UnassignPropertyAction $unassignPropertyAction,
        DeactivateOwnerAction $deactivateOwnerAction,
    ): void {
        $this->assignPropertyAction = $assignPropertyAction;
        $this->unassignPropertyAction = $unassignPropertyAction;
        $this->deactivateOwnerAction = $deactivateOwnerAction;
    }

    public function mount(Owner $owner): void
    {
        $this->owner = $owner;
        $this->fillFormFromModel();
    }

    private function fillFormFromModel(): void
    {
        $this->coprop1Name = $this->owner->coprop1_name;
        $this->coprop1Dni = $this->owner->coprop1_dni;
        $this->coprop1Phone = $this->owner->coprop1_phone ?? '';
        $this->coprop1Email = $this->owner->coprop1_email;
        $this->coprop2Name = $this->owner->coprop2_name ?? '';
        $this->coprop2Dni = $this->owner->coprop2_dni ?? '';
        $this->coprop2Phone = $this->owner->coprop2_phone ?? '';
        $this->coprop2Email = $this->owner->coprop2_email ?? '';
    }

    public function saveOwner(): void
    {
        $this->validate([
            'coprop1Name' => 'required|string|max:255',
            'coprop1Dni' => 'required|string|max:20',
            'coprop1Phone' => 'nullable|string|max:20',
            'coprop1Email' => 'required|email|max:255',
            'coprop2Name' => 'nullable|string|max:255',
            'coprop2Dni' => 'nullable|string|max:20',
            'coprop2Phone' => 'nullable|string|max:20',
            'coprop2Email' => 'nullable|email|max:255',
        ]);

        $this->owner->update([
            'coprop1_name' => $this->coprop1Name,
            'coprop1_dni' => $this->coprop1Dni,
            'coprop1_phone' => $this->coprop1Phone ?: null,
            'coprop1_email' => $this->coprop1Email,
            'coprop2_name' => $this->coprop2Name ?: null,
            'coprop2_dni' => $this->coprop2Dni ?: null,
            'coprop2_phone' => $this->coprop2Phone ?: null,
            'coprop2_email' => $this->coprop2Email ?: null,
        ]);

        $this->showEditForm = false;
        $this->owner->refresh();
    }

    public function cancelEdit(): void
    {
        $this->fillFormFromModel();
        $this->showEditForm = false;
    }

    public function assignProperty(): void
    {
        $this->validate([
            'assignPropertyId' => 'required|exists:properties,id',
            'assignStartDate' => 'required|date',
        ]);

        $property = Property::findOrFail($this->assignPropertyId);

        try {
            $this->assignPropertyAction->execute($property, $this->owner, $this->assignStartDate);
            $this->showAssignForm = false;
            $this->assignPropertyId = '';
            $this->assignStartDate = '';
            $this->errorMessage = '';
            $this->owner->refresh();
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        }
    }

    public function startUnassign(int $assignmentId): void
    {
        $this->unassigningId = $assignmentId;
        $this->unassignEndDate = now()->format('Y-m-d');
    }

    public function confirmUnassign(): void
    {
        $this->validate(['unassignEndDate' => 'required|date']);

        $assignment = PropertyAssignment::findOrFail($this->unassigningId);

        try {
            $this->unassignPropertyAction->execute($assignment, $this->unassignEndDate);
            $this->unassigningId = null;
            $this->unassignEndDate = '';
            $this->errorMessage = '';
            $this->owner->refresh();
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        }
    }

    public function cancelUnassign(): void
    {
        $this->unassigningId = null;
        $this->unassignEndDate = '';
    }

    public function deactivateOwner(): void
    {
        $this->deactivateOwnerAction->execute($this->owner);
        $this->owner->refresh();
    }

    public function render(): View
    {
        $assignments = $this->owner->assignments()
            ->with(['property.location'])
            ->orderByRaw('end_date IS NULL DESC')
            ->orderBy('start_date', 'desc')
            ->get();

        $availableProperties = Property::whereDoesntHave('activeAssignments')
            ->with('location')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.owners.detail', [
            'assignments' => $assignments,
            'availableProperties' => $availableProperties,
        ]);
    }
}
