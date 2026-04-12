<?php

namespace App\Support;

final class OwnerAuditFieldLabel
{
    public static function for(string $field): string
    {
    return match ($field) {
        'coprop1_name' => __('admin.owners.form.coprop1_name'),
        'coprop1_dni' => __('admin.owners.form.coprop1_dni'),
        'coprop1_phone' => __('admin.owners.form.coprop1_phone'),
        'coprop1_email' => __('admin.owners.form.coprop1_email'),
        'language' => __('admin.owners.form.language'),
        'coprop2_name' => __('admin.owners.form.coprop2_name'),
        'coprop2_dni' => __('admin.owners.form.coprop2_dni'),
        'coprop2_phone' => __('admin.owners.form.coprop2_phone'),
        'coprop2_email' => __('admin.owners.form.coprop2_email'),
        'accepted_terms_at' => __('admin.owners.columns.terms_accepted'),
        default => $field,
    };
    }
}
