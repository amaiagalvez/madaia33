<?php

namespace App\Actions\Properties;

use App\Actions\Owners\CreateOwnerAction;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignPropertyAction
{
    public function __construct(
        private readonly CreateOwnerAction $createOwnerAction,
    ) {}

    /**
     * @throws ValidationException
     */
    public function assertNoActiveAssignment(int $propertyId, ?int $ignoreAssignmentId = null): void
    {
        $query = PropertyAssignment::query()
            ->where('property_id', $propertyId)
            ->whereNull('end_date');

        if ($ignoreAssignmentId !== null) {
            $query->whereKeyNot($ignoreAssignmentId);
        }

        $hasActiveAssignment = $query
            ->lockForUpdate()
            ->exists();

        if ($hasActiveAssignment) {
            throw ValidationException::withMessages([
                'property' => __('La propiedad ya tiene una propietaria activa. Cierra la asignación anterior antes de asignar una nueva.'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function execute(Property $property, Owner $owner, string $startDate): PropertyAssignment
    {
        return DB::transaction(function () use ($property, $owner, $startDate): PropertyAssignment {
            $this->assertNoActiveAssignment((int) $property->id);

            $assignment = PropertyAssignment::create([
                'property_id' => $property->id,
                'owner_id' => $owner->id,
                'start_date' => $startDate,
                'end_date' => null,
                'admin_validated' => false,
                'owner_validated' => false,
            ]);

            $owner->user()->update(['is_active' => true]);

            $this->createOwnerAction->sendWelcomeMailToOwner($owner);

            return $assignment;
        });
    }
}
