<?php

namespace App\Validations;

use Illuminate\Validation\Rule;

class OwnerFormValidation
{
    /**
     * @return array<string, mixed>
     */
    public static function profileUpdateRules(int $userId): array
    {
        return [
            'coprop1_name' => ['required', 'string', 'max:255'],
            'coprop1_surname' => ['nullable', 'string', 'max:255'],
            'coprop1_dni' => ['nullable', 'string', 'max:20'],
            'coprop1_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'coprop1_phone' => ['nullable', 'string', 'max:20'],
            'language' => ['required', 'string', 'in:eu,es'],
            'coprop2_name' => ['nullable', 'string', 'max:255'],
            'coprop2_surname' => ['nullable', 'string', 'max:255'],
            'coprop2_dni' => ['nullable', 'string', 'max:20'],
            'coprop2_phone' => ['nullable', 'string', 'max:20'],
            'coprop2_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminEditRules(int $userId): array
    {
        return [
            'editCoprop1Name' => ['required', 'string', 'max:255'],
            'editCoprop1Surname' => ['nullable', 'string', 'max:255'],
            'editCoprop1Dni' => ['nullable', 'string', 'max:20'],
            'editCoprop1Phone' => ['nullable', 'string', 'max:20'],
            'editCoprop1Email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'editLanguage' => ['required', 'string', 'in:eu,es'],
            'editCoprop2Name' => ['nullable', 'string', 'max:255'],
            'editCoprop2Surname' => ['nullable', 'string', 'max:255'],
            'editCoprop2Dni' => ['nullable', 'string', 'max:20'],
            'editCoprop2Phone' => ['nullable', 'string', 'max:20'],
            'editCoprop2Email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function adminEditAttributes(): array
    {
        return [
            'editCoprop1Name' => __('admin.owners.form.coprop1_name'),
            'editCoprop1Surname' => __('admin.owners.form.coprop1_surname'),
            'editCoprop1Dni' => __('admin.owners.form.coprop1_dni'),
            'editCoprop1Phone' => __('admin.owners.form.coprop1_phone'),
            'editCoprop1Email' => __('admin.owners.form.coprop1_email'),
            'editLanguage' => __('admin.owners.form.language'),
            'editCoprop2Name' => __('admin.owners.form.coprop2_name'),
            'editCoprop2Surname' => __('admin.owners.form.coprop2_surname'),
            'editCoprop2Dni' => __('admin.owners.form.coprop2_dni'),
            'editCoprop2Phone' => __('admin.owners.form.coprop2_phone'),
            'editCoprop2Email' => __('admin.owners.form.coprop2_email'),
        ];
    }
}
