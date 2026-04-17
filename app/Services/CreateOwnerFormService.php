<?php

namespace App\Services;

/**
 * Handles form data preparation and validation for owner creation.
 *
 * Extracts and transforms form input into structured data for CreateOwnerAction.
 */
class CreateOwnerFormService
{
    /**
     * Validate assignment date ranges (end_date >= start_date).
     *
     * @param  array<int, array{start_date: string, end_date: string|null}>  $assignments
     * @return array<string, string>
     */
    public function validateAssignmentDates(array $assignments): array
    {
        $errors = [];

        foreach ($assignments as $index => $assignment) {
            if ($assignment['end_date'] !== '' && $assignment['end_date'] !== null && $assignment['end_date'] < $assignment['start_date']) {
                $errors["newAssignments.$index.end_date"] = __('validation.after_or_equal', [
                    'attribute' => __('admin.owners.end_date'),
                    'date' => __('admin.owners.start_date'),
                ]);
            }
        }

        return $errors;
    }

    /**
     * Transform validated form data into action parameter format.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function prepareOwnerData(array $validated): array
    {
        return [
            'owner_id' => $this->nullableInt($validated['ownerId'] ?? null),
            'coprop1_name' => $validated['coprop1Name'],
            'coprop1_surname' => $this->nullableString($validated['coprop1Surname'] ?? null),
            'coprop1_dni' => $this->nullableString($validated['coprop1Dni'] ?? null),
            'coprop1_phone' => $this->nullableString($validated['coprop1Phone'] ?? null),
            'coprop1_email' => $validated['coprop1Email'],
            'language' => $validated['language'],
            'coprop2_name' => $this->nullableString($validated['coprop2Name'] ?? null),
            'coprop2_surname' => $this->nullableString($validated['coprop2Surname'] ?? null),
            'coprop2_dni' => $this->nullableString($validated['coprop2Dni'] ?? null),
            'coprop2_phone' => $this->nullableString($validated['coprop2Phone'] ?? null),
            'coprop2_email' => $this->nullableString($validated['coprop2Email'] ?? null),
            'assignments' => $this->normalizeAssignments($validated['newAssignments'] ?? []),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return array<int, array{property_id: int, start_date: string, end_date: ?string}>
     */
    private function normalizeAssignments(mixed $newAssignments): array
    {
        if (! is_array($newAssignments)) {
            return [];
        }

        return collect($newAssignments)
            ->map(static function (array $assignment): array {
                return [
                    'property_id' => (int) $assignment['property_id'],
                    'start_date' => $assignment['start_date'],
                    'end_date' => $assignment['end_date'] !== '' ? $assignment['end_date'] : null,
                ];
            })
            ->all();
    }
}
