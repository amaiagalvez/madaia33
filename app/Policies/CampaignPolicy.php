<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignLocation;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCampaignAdmin($user);
    }

    public function view(User $user, Campaign $campaign): bool
    {
        // Campaign id=1 is reserved for direct messages and only visible to SUPER_ADMIN
        if ($campaign->id === 1 && ! $user->hasRole(Role::SUPER_ADMIN)) {
            return false;
        }

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
            'all-only' => ! $this->campaignHasLocationFilters($campaign),
            'managed-locations' => $this->hasManagedLocationAccess($user, $campaign),
            default => false,
        };
    }

    private function hasManagedLocationAccess(User $user, Campaign $campaign): bool
    {
        if (! $this->campaignHasLocationFilters($campaign)) {
            return false;
        }

        $managedLocationIds = $user->managedLocations()->pluck('locations.id')->all();

        if ($managedLocationIds === []) {
            return false;
        }

        return $this->campaignUsesOnlyManagedLocations($campaign, $managedLocationIds);
    }

    private function campaignHasLocationFilters(Campaign $campaign): bool
    {
        if ($campaign->relationLoaded('locations')) {
            return $campaign->locations
                ->contains(static fn (CampaignLocation $location): bool => $location->deleted_at === null);
        }

        return CampaignLocation::query()
            ->where('campaign_id', $campaign->id)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * @param  array<int, int>  $managedLocationIds
     */
    private function campaignUsesOnlyManagedLocations(Campaign $campaign, array $managedLocationIds): bool
    {
        $locationIds = $campaign->relationLoaded('locations')
            ? $campaign->locations
                ->filter(static fn (CampaignLocation $location): bool => $location->deleted_at === null)
                ->pluck('location_id')
                ->map(static fn (int $locationId): int => $locationId)
                ->unique()
                ->values()
                ->all()
            : CampaignLocation::query()
                ->where('campaign_id', $campaign->id)
                ->whereNull('deleted_at')
                ->pluck('location_id')
                ->map(static fn (int $locationId): int => $locationId)
                ->unique()
                ->values()
                ->all();

        if ($locationIds === []) {
            return false;
        }

        $managedMap = array_fill_keys($managedLocationIds, true);

        foreach ($locationIds as $locationId) {
            if (! isset($managedMap[$locationId])) {
                return false;
            }
        }

        return true;
    }
}
