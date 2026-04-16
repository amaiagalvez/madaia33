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
            $this->resetContactErrorFlags($owner, 'coprop1', 'email');
        }

        if ($owner->isDirty('coprop2_email')) {
            $this->resetContactErrorFlags($owner, 'coprop2', 'email');
        }

        if ($owner->isDirty('coprop1_phone')) {
            $this->resetContactErrorFlags($owner, 'coprop1', 'phone');
        }

        if ($owner->isDirty('coprop2_phone')) {
            $this->resetContactErrorFlags($owner, 'coprop2', 'phone');
        }
    }

    private function resetContactErrorFlags(Owner $owner, string $slot, string $channel): void
    {
        $owner->{$slot . '_' . $channel . '_error_count'} = 0;
        $owner->{$slot . '_' . $channel . '_invalid'} = false;
    }
}
