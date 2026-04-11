<?php

namespace App\Actions\Properties;

use Carbon\CarbonImmutable;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($assignment, $endDate): void {
            $assignment->update(['end_date' => $endDate]);

            $ownerHasActiveAssignments = PropertyAssignment::query()
                ->where('owner_id', $assignment->owner_id)
                ->whereNull('end_date')
                ->exists();

            if (! $ownerHasActiveAssignments) {
                $assignment->owner()->first()?->user()->update(['is_active' => false]);
            }
        });

        return $assignment->fresh();
    }
}
