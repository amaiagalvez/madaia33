<?php

namespace App\Actions\Properties;

use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignPropertyAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Property $property, Owner $owner, string $startDate): PropertyAssignment
    {
        return DB::transaction(function () use ($property, $owner, $startDate): PropertyAssignment {
            $hasActiveAssignment = PropertyAssignment::query()
                ->where('property_id', $property->id)
                ->whereNull('end_date')
                ->lockForUpdate()
                ->exists();

            if ($hasActiveAssignment) {
                throw ValidationException::withMessages([
                    'property' => __('La propiedad ya tiene una propietaria activa. Cierra la asignación anterior antes de asignar una nueva.'),
                ]);
            }

            $assignment = PropertyAssignment::create([
                'property_id' => $property->id,
                'owner_id' => $owner->id,
                'start_date' => $startDate,
                'end_date' => null,
                'admin_validated' => false,
                'owner_validated' => false,
            ]);

            $owner->user()->update(['is_active' => true]);

            return $assignment;
        });
    }
}
