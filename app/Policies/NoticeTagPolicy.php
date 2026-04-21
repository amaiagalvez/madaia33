<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\NoticeTag;

class NoticeTagPolicy
{
    public function create(User $user): bool
    {
        return $this->canManageTags($user);
    }

    public function update(User $user, NoticeTag $noticeTag): bool
    {
        return $this->canManageTags($user);
    }

    public function delete(User $user, NoticeTag $noticeTag): bool
    {
        return $this->canManageTags($user);
    }

    private function canManageTags(User $user): bool
    {
        return $user->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
        ]);
    }
}
