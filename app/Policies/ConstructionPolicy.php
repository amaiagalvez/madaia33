<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Construction;

class ConstructionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageConstructions();
    }

    public function create(User $user): bool
    {
        return $user->canManageConstructions();
    }

    public function update(User $user, Construction $construction): bool
    {
        return $user->canManageConstructions();
    }

    public function delete(User $user, Construction $construction): bool
    {
        return $user->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
        ]);
    }
}
