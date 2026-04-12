<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait ManagesOwnerAssignments
{
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

        if (! is_array($edit)) {
            return;
        }

        $this->validate([
            "assignmentEdits.$assignmentId.start_date" => ['required', 'date'],
            "assignmentEdits.$assignmentId.end_date" => ['nullable', 'date'],
        ]);

        if ($this->isInvalidDateRange($edit['start_date'], $edit['end_date'])) {
            $this->addError("assignmentEdits.$assignmentId.end_date", __('validation.after_or_equal', ['attribute' => __('admin.owners.end_date'), 'date' => __('admin.owners.start_date')]));

            return;
        }

        if ($this->shouldReopenAssignment($assignment, $edit)) {
            if (! $this->reopenAssignment($assignment, $edit)) {
                return;
            }

            $this->loadAssignmentEdits((int) $assignment->owner_id);

            return;
        }

        $this->updateAssignmentData($assignment, $edit);

        if (! $this->syncAssignmentEndDate($assignment, $edit)) {
            return;
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

        if ($this->isInvalidDateRange($validated['inlineStartDate'], $validated['inlineEndDate'])) {
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
            $this->mapValidationExceptionToRowError($e);
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

    /**
     * @param  array{start_date: string, end_date: string, admin_validated: bool, owner_validated: bool}  $edit
     */
    private function shouldReopenAssignment(PropertyAssignment $assignment, array $edit): bool
    {
        return $assignment->end_date !== null && $edit['end_date'] === '';
    }

    private function isInvalidDateRange(string $startDate, string $endDate): bool
    {
        return $endDate !== '' && $endDate < $startDate;
    }

    /**
     * @param  array{start_date: string, end_date: string, admin_validated: bool, owner_validated: bool}  $edit
     */
    private function reopenAssignment(PropertyAssignment $assignment, array $edit): bool
    {
        try {
            DB::transaction(function () use ($assignment, $edit): void {
                $this->assignPropertyAction->assertNoActiveAssignment((int) $assignment->property_id, (int) $assignment->id);

                $assignment->update([
                    'start_date' => $edit['start_date'],
                    'end_date' => null,
                    'admin_validated' => (bool) $assignment->admin_validated,
                    'owner_validated' => (bool) $assignment->owner_validated,
                ]);

                $assignment->owner()->first()?->user()->update(['is_active' => true]);
            });
        } catch (ValidationException $e) {
            $this->mapValidationExceptionToRowError($e);

            return false;
        }

        return true;
    }

    /**
     * @param  array{start_date: string, end_date: string, admin_validated: bool, owner_validated: bool}  $edit
     */
    private function updateAssignmentData(PropertyAssignment $assignment, array $edit): void
    {
        $willBeClosed = $assignment->end_date !== null || $edit['end_date'] !== '';

        $assignment->update([
            'start_date' => $edit['start_date'],
            'admin_validated' => $willBeClosed ? $assignment->admin_validated : (bool) $edit['admin_validated'],
            'owner_validated' => $willBeClosed ? $assignment->owner_validated : (bool) $edit['owner_validated'],
        ]);
    }

    /**
     * @param  array{start_date: string, end_date: string, admin_validated: bool, owner_validated: bool}  $edit
     */
    private function syncAssignmentEndDate(PropertyAssignment $assignment, array $edit): bool
    {
        $isAlreadyClosed = $assignment->end_date !== null;
        $isClosingAssignment = $edit['end_date'] !== '';

        if (! $isAlreadyClosed && $isClosingAssignment) {
            try {
                $this->unassignPropertyAction->execute($assignment, $edit['end_date']);
            } catch (ValidationException $e) {
                $this->mapValidationExceptionToRowError($e);

                return false;
            }

            return true;
        }

        if (! $isClosingAssignment) {
            $assignment->update(['end_date' => null]);
        }

        return true;
    }

    private function mapValidationExceptionToRowError(ValidationException $exception): void
    {
        $this->rowErrorMessage = (string) collect($exception->errors())->flatten()->first();
    }
}
