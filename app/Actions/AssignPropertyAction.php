<?php

namespace App\Actions;

use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Validation\ValidationException;

class AssignPropertyAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Property $property, Owner $owner, string $startDate): PropertyAssignment
    {
        $hasActiveAssignment = $property->activeAssignments()->exists();

        if ($hasActiveAssignment) {
            throw ValidationException::withMessages([
                'property' => __('La propiedad ya tiene una propietaria activa. Cierra la asignación anterior antes de asignar una nueva.'),
            ]);
        }

        return PropertyAssignment::create([
            'property_id' => $property->id,
            'owner_id' => $owner->id,
            'start_date' => $startDate,
            'end_date' => null,
            'admin_validated' => false,
            'owner_validated' => false,
        ]);
    }
}
