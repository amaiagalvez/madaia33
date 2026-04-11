<?php

namespace App\Actions\Owners;

use App\Models\Owner;

class DeactivateOwnerAction
{
    public function execute(Owner $owner): Owner
    {
        $owner->user()->update(['is_active' => false]);

        return $owner;
    }
}
