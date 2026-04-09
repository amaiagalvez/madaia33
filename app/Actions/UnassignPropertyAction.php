<?php

namespace App\Actions;

use App\Models\PropertyAssignment;
use Illuminate\Validation\ValidationException;

class UnassignPropertyAction
{
    /**
     * @throws ValidationException
     */
    public function execute(PropertyAssignment $assignment, string $endDate): PropertyAssignment
    {
        if ($assignment->end_date !== null) {
            throw ValidationException::withMessages([
                'assignment' => __('Esta asignación ya está cerrada.'),
            ]);
        }

        $assignment->update(['end_date' => $endDate]);

        return $assignment->fresh();
    }
}
