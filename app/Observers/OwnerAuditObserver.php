<?php

namespace App\Observers;

use App\Models\Owner;
use App\Models\OwnerAuditLog;
use Illuminate\Support\Facades\Auth;

class OwnerAuditObserver
{
    public function updating(Owner $owner): void
    {
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
}
