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
            'coprop1_name' => $validated['coprop1Name'],
            'coprop1_dni' => $validated['coprop1Dni'],
            'coprop1_phone' => $validated['coprop1Phone'] ?: null,
            'coprop1_email' => $validated['coprop1Email'],
            'language' => $validated['language'],
            'coprop2_name' => $validated['coprop2Name'] ?: null,
            'coprop2_dni' => $validated['coprop2Dni'] ?: null,
            'coprop2_phone' => $validated['coprop2Phone'] ?: null,
            'coprop2_email' => $validated['coprop2Email'] ?: null,
            'assignments' => collect(is_array($validated['newAssignments']) ? $validated['newAssignments'] : [])
                ->map(static function (array $assignment): array {
                    return [
                        'property_id' => (int) $assignment['property_id'],
                        'start_date' => $assignment['start_date'],
                        'end_date' => $assignment['end_date'] !== '' ? $assignment['end_date'] : null,
                    ];
                })
                ->all(),
        ];
    }
}
