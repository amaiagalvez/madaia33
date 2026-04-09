<?php

namespace App\Actions;

use App\Models\PropertyAssignment;
use Carbon\CarbonImmutable;
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

        $startDate = CarbonImmutable::parse((string) $assignment->start_date);
        $resolvedEndDate = CarbonImmutable::parse($endDate);

        if ($resolvedEndDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'assignment' => __('La fecha de fin no puede ser anterior a la fecha de inicio.'),
            ]);
        }

        $assignment->update(['end_date' => $endDate]);

        return $assignment->fresh();
    }
}
