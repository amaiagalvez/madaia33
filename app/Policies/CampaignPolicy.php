<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Campaign;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCampaignAdmin($user);
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $this->isCampaignAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isCampaignAdmin($user);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $this->isCampaignAdmin($user) && in_array($campaign->status, ['draft', 'scheduled'], true);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $this->isCampaignAdmin($user) && in_array($campaign->status, ['draft', 'scheduled'], true);
    }

    public function send(User $user, Campaign $campaign): bool
    {
        if (! $this->isCampaignAdmin($user)) {
            return false;
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            if ($campaign->recipient_filter === 'all') {
                return false;
            }

            if (! str_contains((string) $campaign->recipient_filter, ':')) {
                return false;
            }

            [, $locationCode] = explode(':', (string) $campaign->recipient_filter, 2);

            return $user->managedLocations()->where('code', $locationCode)->exists();
        }

        return true;
    }

    public function duplicate(User $user, Campaign $campaign): bool
    {
        return $this->isCampaignAdmin($user);
    }

    private function isCampaignAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
            Role::COMMUNITY_ADMIN,
        ]);
    }
}
