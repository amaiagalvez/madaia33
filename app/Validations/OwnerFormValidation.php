<?php

namespace App\Validations;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class OwnerFormValidation
{
    /**
     * @return array<string, mixed>
     */
    public static function profileUpdateRules(int $userId): array
    {
        return self::ownerRules($userId, self::profileFieldMap());
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminEditRules(int $userId): array
    {
        return self::ownerRules($userId, self::adminEditFieldMap());
    }

    /**
     * @return array<string, mixed>
     */
    public static function createRules(): array
    {
        return [
            ...self::ownerRules(null, self::createFieldMap()),
            'coprop1Dni' => ['nullable', 'string', 'max:20', 'unique:owners,coprop1_dni'],
            'newAssignments' => ['required', 'array', 'min:1'],
            'newAssignments.*.property_id' => ['required', 'exists:properties,id'],
            'newAssignments.*.start_date' => ['required', 'date'],
            'newAssignments.*.end_date' => ['nullable', 'date'],
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
            'editCoprop1HasWhatsapp' => __('admin.owners.form.has_whatsapp'),
            'editCoprop1Email' => __('admin.owners.form.coprop1_email'),
            'editLanguage' => __('admin.owners.form.language'),
            'editCoprop2Name' => __('admin.owners.form.coprop2_name'),
            'editCoprop2Surname' => __('admin.owners.form.coprop2_surname'),
            'editCoprop2Dni' => __('admin.owners.form.coprop2_dni'),
            'editCoprop2Phone' => __('admin.owners.form.coprop2_phone'),
            'editCoprop2HasWhatsapp' => __('admin.owners.form.has_whatsapp'),
            'editCoprop2Email' => __('admin.owners.form.coprop2_email'),
        ];
    }

    /**
     * @param  array<string, string>  $fieldMap
     * @return array<string, mixed>
     */
    private static function ownerRules(?int $userId, array $fieldMap): array
    {
        return [
            $fieldMap['coprop1_name'] => ['required', 'string', 'max:255'],
            $fieldMap['coprop1_surname'] => ['nullable', 'string', 'max:255'],
            $fieldMap['coprop1_dni'] => ['nullable', 'string', 'max:20'],
            $fieldMap['coprop1_email'] => ['nullable', 'email', 'max:255', self::userEmailUniqueRule($userId)],
            $fieldMap['coprop1_phone'] => ['nullable', 'string', 'max:20'],
            $fieldMap['coprop1_has_whatsapp'] => ['nullable', 'boolean'],
            $fieldMap['language'] => ['required', 'string', 'in:eu,es'],
            $fieldMap['coprop2_name'] => ['nullable', 'string', 'max:255'],
            $fieldMap['coprop2_surname'] => ['nullable', 'string', 'max:255'],
            $fieldMap['coprop2_dni'] => ['nullable', 'string', 'max:20'],
            $fieldMap['coprop2_phone'] => ['nullable', 'string', 'max:20'],
            $fieldMap['coprop2_has_whatsapp'] => ['nullable', 'boolean'],
            $fieldMap['coprop2_email'] => ['nullable', 'email', 'max:255'],
        ];
    }

    private static function userEmailUniqueRule(?int $userId): Unique
    {
        $rule = Rule::unique('users', 'email');

        if ($userId === null) {
            return $rule;
        }

        return $rule->ignore($userId);
    }

    /**
     * @return array<string, string>
     */
    private static function profileFieldMap(): array
    {
        return [
            'coprop1_name' => 'coprop1_name',
            'coprop1_surname' => 'coprop1_surname',
            'coprop1_dni' => 'coprop1_dni',
            'coprop1_email' => 'coprop1_email',
            'coprop1_phone' => 'coprop1_phone',
            'coprop1_has_whatsapp' => 'coprop1_has_whatsapp',
            'language' => 'language',
            'coprop2_name' => 'coprop2_name',
            'coprop2_surname' => 'coprop2_surname',
            'coprop2_dni' => 'coprop2_dni',
            'coprop2_phone' => 'coprop2_phone',
            'coprop2_has_whatsapp' => 'coprop2_has_whatsapp',
            'coprop2_email' => 'coprop2_email',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function adminEditFieldMap(): array
    {
        return [
            'coprop1_name' => 'editCoprop1Name',
            'coprop1_surname' => 'editCoprop1Surname',
            'coprop1_dni' => 'editCoprop1Dni',
            'coprop1_email' => 'editCoprop1Email',
            'coprop1_phone' => 'editCoprop1Phone',
            'coprop1_has_whatsapp' => 'editCoprop1HasWhatsapp',
            'language' => 'editLanguage',
            'coprop2_name' => 'editCoprop2Name',
            'coprop2_surname' => 'editCoprop2Surname',
            'coprop2_dni' => 'editCoprop2Dni',
            'coprop2_phone' => 'editCoprop2Phone',
            'coprop2_has_whatsapp' => 'editCoprop2HasWhatsapp',
            'coprop2_email' => 'editCoprop2Email',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function createFieldMap(): array
    {
        return [
            'coprop1_name' => 'coprop1Name',
            'coprop1_surname' => 'coprop1Surname',
            'coprop1_dni' => 'coprop1Dni',
            'coprop1_email' => 'coprop1Email',
            'coprop1_phone' => 'coprop1Phone',
            'coprop1_has_whatsapp' => 'coprop1HasWhatsapp',
            'language' => 'language',
            'coprop2_name' => 'coprop2Name',
            'coprop2_surname' => 'coprop2Surname',
            'coprop2_dni' => 'coprop2Dni',
            'coprop2_phone' => 'coprop2Phone',
            'coprop2_has_whatsapp' => 'coprop2HasWhatsapp',
            'coprop2_email' => 'coprop2Email',
        ];
    }
}
