<?php

namespace App\Observers;

use App\Models\Owner;
use App\Models\OwnerAuditLog;
use Illuminate\Support\Facades\Auth;

class OwnerAuditObserver
{
    public function updating(Owner $owner): void
    {
        $this->resetMessagingContactErrorsWhenContactChanges($owner);

        $dirty = $owner->getDirty();
        $changedByUserId = Auth::id();

        foreach ($dirty as $field => $newValue) {
            OwnerAuditLog::create([
                'owner_id' => $owner->id,
                'changed_by_user_id' => $changedByUserId,
                'field' => $field,
                'old_value' => (string) ($owner->getOriginal($field) ?? ''),
                'new_value' => (string) ($newValue ?? ''),
            ]);
        }
    }

    private function resetMessagingContactErrorsWhenContactChanges(Owner $owner): void
    {
        if ($owner->isDirty('coprop1_email')) {
            $owner->coprop1_email_error_count = 0;
            $owner->coprop1_email_invalid = false;
        }

        if ($owner->isDirty('coprop2_email')) {
            $owner->coprop2_email_error_count = 0;
            $owner->coprop2_email_invalid = false;
        }

        if ($owner->isDirty('coprop1_phone')) {
            $owner->coprop1_phone_error_count = 0;
            $owner->coprop1_phone_invalid = false;
        }

        if ($owner->isDirty('coprop2_phone')) {
            $owner->coprop2_phone_error_count = 0;
            $owner->coprop2_phone_invalid = false;
        }
    }
}
