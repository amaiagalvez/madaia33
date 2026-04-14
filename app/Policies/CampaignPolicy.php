<?php

namespace App\Policies;

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
        return $this->canAccessCampaign($user, $campaign);
    }

    public function create(User $user): bool
    {
        return $this->isCampaignAdmin($user);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $this->canAccessCampaign($user, $campaign)
            && in_array($campaign->status, ['draft', 'scheduled'], true);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $this->canAccessCampaign($user, $campaign)
            && in_array($campaign->status, ['draft', 'scheduled'], true);
    }

    public function send(User $user, Campaign $campaign): bool
    {
        return $this->canAccessCampaign($user, $campaign);
    }

    public function duplicate(User $user, Campaign $campaign): bool
    {
        return $this->canAccessCampaign($user, $campaign);
    }

    private function isCampaignAdmin(User $user): bool
    {
        return $user->canManageCampaigns();
    }

    private function canAccessCampaign(User $user, Campaign $campaign): bool
    {
        return match ($user->campaignAccessScope()) {
            'all-filters' => true,
            'all-only' => $campaign->recipient_filter === 'all',
            'managed-locations' => $this->hasManagedLocationAccess($user, (string) $campaign->recipient_filter),
            default => false,
        };
    }

    private function hasManagedLocationAccess(User $user, string $recipientFilter): bool
    {
        if ($recipientFilter === 'all' || ! str_contains($recipientFilter, ':')) {
            return false;
        }

        [$type, $locationCode] = explode(':', $recipientFilter, 2);

        if (! in_array($type, ['portal', 'garage'], true)) {
            return false;
        }

        return $user->managedLocations()->where('code', $locationCode)->exists();
    }
}
